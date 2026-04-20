<?php

namespace App\Filament\Ops\Resources\CanonicalProductResource\RelationManagers;

use App\Models\Knowledge\CanonicalProduct;
use App\Models\Knowledge\Requirement;
use App\Support\Knowledge\RequirementCoverage;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RequirementsRelationManager extends RelationManager
{
    protected static string $relationship = 'requirements';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.canonical_product_requirements.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('ops.resources.canonical_product_requirements.empty_heading'))
            ->emptyStateDescription(__('ops.resources.canonical_product_requirements.empty_description'))
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('ops.resources.requirement.code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('ops.resources.requirement.type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('ops.resources.requirement.name'))
                    ->limit(40),
                Tables\Columns\TextColumn::make('evidence_status')
                    ->label(__('ops.resources.canonical_product_requirements.columns.evidence_status'))
                    ->badge()
                    ->formatStateUsing(function (Requirement $record): string {
                        $status = $this->resolveEvidenceStatus($record);

                        return __('ops.resources.canonical_product_requirements.evidence_status.'.$status);
                    })
                    ->color(function (Requirement $record): string {
                        $status = $this->resolveEvidenceStatus($record);

                        return match ($status) {
                            'covered' => 'success',
                            'not_covered' => 'danger',
                            default => 'gray',
                        };
                    }),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelectOptionsQuery(fn ($query) => $query->whereIn('type', Requirement::SKU_ATTACHABLE_TYPES))
                    ->label(__('ops.resources.canonical_product_requirements.actions.attach'))
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }

    private function resolveEvidenceStatus(Requirement $requirement): string
    {
        /** @var CanonicalProduct $owner */
        $owner = $this->getOwnerRecord();
        $expectedTypes = RequirementCoverage::expectedSkuDocumentTypes($requirement);
        if ($expectedTypes === []) {
            return 'not_applicable';
        }

        $covered = $owner->documents()
            ->whereIn('document_type', $expectedTypes)
            ->where('status', 'provided')
            ->exists();

        return $covered ? 'covered' : 'not_covered';
    }
}
