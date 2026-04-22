import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

type Portal = {
    id: string;
    url: string;
    label: string;
    description: string;
};

export default function Dashboard({
    portals,
    hubTitle,
    hubIntro,
    noPortals,
}: {
    portals: Portal[];
    hubTitle: string;
    hubIntro: string;
    noPortals: string;
}) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {hubTitle}
                </h2>
            }
        >
            <Head title={hubTitle} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <p className="mb-6 text-sm text-gray-600">{hubIntro}</p>

                    {portals.length === 0 ? (
                        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div className="p-6 text-gray-700">{noPortals}</div>
                        </div>
                    ) : (
                        <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {portals.map((portal) => (
                                <li key={portal.id}>
                                    <a
                                        href={portal.url}
                                        className="block h-full rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md"
                                    >
                                        <span className="text-base font-semibold text-gray-900">
                                            {portal.label}
                                        </span>
                                        <p className="mt-2 text-sm text-gray-600">
                                            {portal.description}
                                        </p>
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
