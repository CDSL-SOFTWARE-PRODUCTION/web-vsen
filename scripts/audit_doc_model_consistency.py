#!/usr/bin/env python3
"""So khớp doc/ với model/ — không phụ thuộc runtime app."""
from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def load_yaml_entities() -> set[str]:
    import yaml

    with (ROOT / "model" / "entities.yaml").open(encoding="utf-8") as f:
        data = yaml.safe_load(f)
    return {e["id"] for e in data.get("entities", [])}


def load_yaml_constraints() -> set[str]:
    import yaml

    with (ROOT / "model" / "constraints.yaml").open(encoding="utf-8") as f:
        data = yaml.safe_load(f)
    return {c["id"] for c in data.get("constraints", [])}


def erd_entity_names(md: str) -> set[str]:
    # File có nhiều ```mermaid — chỉ lấy khối có erDiagram (DATABASE ERD).
    m = re.search(r"```mermaid\s*\nerDiagram", md)
    if not m:
        return set()
    end = md.find("```", m.end())
    block = md[m.start() : end if end != -1 else len(md)]
    names = set()
    for m in re.finditer(r"^\s{4}([A-Za-z][A-Za-z0-9_]*)\s+\{", block, re.MULTILINE):
        names.add(m.group(1))
    return names


def doc_constraint_refs(md: str) -> set[str]:
    return set(re.findall(r"\b(C-[A-Z]+-[0-9]+)\b", md))


def main() -> int:
    entities = load_yaml_entities()
    constraints = load_yaml_constraints()
    sys_arch = (ROOT / "doc" / "system_architecture.md").read_text(encoding="utf-8")
    biz = (ROOT / "doc" / "guide.md").read_text(encoding="utf-8")

    erd = erd_entity_names(sys_arch)
    in_erd_not_model = sorted(erd - entities)
    in_model_not_erd = sorted(entities - erd)

    doc_refs = doc_constraint_refs(sys_arch) | doc_constraint_refs(biz)
    ref_not_in_yaml = sorted(doc_refs - constraints)

    ord_nums = sorted(
        int(x.split("-")[2])
        for x in constraints
        if x.startswith("C-ORD-") and len(x.split("-")) > 2 and x.split("-")[2].isdigit()
    )
    inv_nums = sorted(
        int(x.split("-")[2])
        for x in constraints
        if x.startswith("C-INV-") and len(x.split("-")) > 2 and x.split("-")[2].isdigit()
    )
    range_issues: list[str] = []
    demand_section = sys_arch.split("## 1.", 1)[-1].split("## 2.", 1)[0]
    has_ord_range = "`C-ORD-001` … `C-ORD-008`" in demand_section or "C-ORD-007" in demand_section
    if ord_nums and max(ord_nums) >= 7 and not has_ord_range:
        range_issues.append(
            f"§1 Demand: thiếu C-ORD-007/C-ORD-008 trong bullet (YAML có tới C-ORD-{max(ord_nums):03d})"
        )
    inv_ok = "`C-INV-001` … `C-INV-006`" in sys_arch or "`C-INV-006`" in sys_arch.split("## 2.", 1)[-1].split("## 3.", 1)[0]
    if not inv_ok and inv_nums and max(inv_nums) > 4:
        range_issues.append(
            f"§2 Inventory: bullet không phủ C-INV tới {max(inv_nums):03d} (kiểm tra dòng Constraints)"
        )
    if "C-SUP-001" in constraints and "C-SUP-001" not in sys_arch:
        range_issues.append("constraints.yaml có C-SUP-001 nhưng system_architecture.md chưa nhắc (Supply / AwardTender)")

    print("=== audit_doc_model_consistency ===")
    print("ERD not in entities.yaml:", in_erd_not_model or "(none)")
    print("entities.yaml not in ERD:", in_model_not_erd or "(none)")
    print("Doc cites constraint ID not in YAML:", ref_not_in_yaml or "(none)")
    print("Layer vs YAML range notes:")
    for x in range_issues:
        print(" -", x)
    if not range_issues:
        print(" - (none)")

    failed = bool(in_erd_not_model or in_model_not_erd or ref_not_in_yaml or range_issues)
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
