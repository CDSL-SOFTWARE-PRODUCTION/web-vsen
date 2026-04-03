import { useTranslate } from '@/helpers';

export default function StatsBar() {
  const { __ } = useTranslate();
  return (
    <section className="py-12 bg-white">
      <div className="max-w-7xl mx-auto px-4 flex flex-wrap justify-between items-center border-b border-gray-100 pb-12 gap-8">
        <div className="flex-1 min-w-[300px]">
          <h2 className="text-4xl font-bold text-ge-blue italic tracking-tighter">VSENS Medical</h2>
          <p className="text-gray-500 mt-2 font-medium">{__('Empowering clinicians with data-driven insights.')}</p>
        </div>
        <div className="flex gap-12 flex-wrap">
          <div>
            <div className="text-3xl font-bold text-gray-900">1000+</div>
            <div className="text-sm text-gray-500 uppercase tracking-widest">{__('Medical Devices')}</div>
          </div>
          <div>
            <div className="text-3xl font-bold text-gray-900">200+</div>
            <div className="text-sm text-gray-500 uppercase tracking-widest">{__('Hospitals Served')}</div>
          </div>
          <div>
            <div className="text-3xl font-bold text-gray-900">20+</div>
            <div className="text-sm text-gray-500 uppercase tracking-widest">{__('Countries')}</div>
          </div>
        </div>
      </div>
    </section>
  );
}
