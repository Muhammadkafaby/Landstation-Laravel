function statusTone(status, selected) {
    if (selected) {
        return 'border-emerald-300 bg-emerald-400 text-zinc-950 shadow-[0_18px_40px_-18px_rgba(52,211,153,0.85)]';
    }

    switch (status) {
        case 'maintenance':
            return 'border-amber-400/60 bg-amber-200/80 text-amber-950';
        case 'reserved':
        case 'occupied':
            return 'border-rose-500/60 bg-rose-200/80 text-rose-950';
        default:
            return 'border-cyan-300/60 bg-cyan-100/85 text-cyan-950';
    }
}

function toneLabel(status) {
    switch (status) {
        case 'maintenance':
            return 'Maintenance';
        case 'reserved':
            return 'Reserved';
        case 'occupied':
            return 'Occupied';
        default:
            return 'Available';
    }
}

function hasManualLayout(service, units) {
    if (service?.layout?.mode !== 'manual_grid') {
        return false;
    }

    return units.some((unit) => unit.layout?.x !== null && unit.layout?.y !== null);
}

function minimumManualCanvasWidth(units, canvasWidth) {
    const minimumUnitWidth = 88;
    const minimumUnitHeight = 72;

    const scale = units.reduce((largestScale, unit) => {
        const unitWidth = Math.max(unit.layout?.w ?? 180, 1);
        const unitHeight = Math.max(unit.layout?.h ?? 110, 1);

        return Math.max(
            largestScale,
            minimumUnitWidth / unitWidth,
            minimumUnitHeight / unitHeight,
        );
    }, 1);

    return Math.ceil(canvasWidth * scale);
}

function PositionedUnitButton({ unit, canvasWidth, canvasHeight, selected, onSelect }) {
    const width = unit.layout?.w ?? 180;
    const height = unit.layout?.h ?? 110;
    const left = ((unit.layout?.x ?? 0) / canvasWidth) * 100;
    const top = ((unit.layout?.y ?? 0) / canvasHeight) * 100;
    const buttonWidth = (width / canvasWidth) * 100;
    const buttonHeight = (height / canvasHeight) * 100;

    return (
        <button
            type="button"
            onClick={() => onSelect(unit.id)}
            className={`group absolute overflow-hidden rounded-[1.75rem] border px-4 py-3 text-left transition duration-200 ease-out hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-emerald-300/70 ${statusTone(unit.status, selected)}`}
            style={{
                left: `${left}%`,
                top: `${top}%`,
                width: `${buttonWidth}%`,
                height: `${buttonHeight}%`,
                transform: `rotate(${unit.layout?.rotation ?? 0}deg)`,
                zIndex: unit.layout?.zIndex ?? 1,
            }}
            aria-pressed={selected}
        >
            <div className="flex h-full flex-col justify-between">
                <div>
                    <p className="text-[10px] font-semibold uppercase tracking-[0.22em] opacity-75">
                        {toneLabel(unit.status)}
                    </p>
                    <h3 className="mt-1 text-sm font-black tracking-[0.08em]">{unit.code}</h3>
                </div>
                <p className="line-clamp-2 text-xs font-medium opacity-80">{unit.name}</p>
            </div>
        </button>
    );
}

function FallbackUnitGrid({ units, selectedUnitId, onSelect }) {
    return (
        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            {units.map((unit) => {
                const selected = selectedUnitId === unit.id;

                return (
                    <button
                        key={unit.id}
                        type="button"
                        onClick={() => onSelect(unit.id)}
                        className={`rounded-[1.5rem] border px-4 py-4 text-left transition duration-200 ease-out hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-emerald-300/70 ${statusTone(unit.status, selected)}`}
                        aria-pressed={selected}
                    >
                        <p className="text-[10px] font-semibold uppercase tracking-[0.22em] opacity-75">
                            {toneLabel(unit.status)}
                        </p>
                        <h3 className="mt-1 text-base font-black tracking-[0.08em]">{unit.name}</h3>
                        <p className="mt-1 text-xs font-medium opacity-80">{unit.code}</p>
                        {unit.zone ? (
                            <p className="mt-3 text-xs opacity-70">{unit.zone}</p>
                        ) : null}
                    </button>
                );
            })}
        </div>
    );
}

export default function UnitLayoutPicker({
    service,
    units,
    selectedUnitId,
    onSelect,
}) {
    const canvasWidth = service?.layout?.canvasWidth ?? 960;
    const canvasHeight = service?.layout?.canvasHeight ?? 640;
    const selectedUnit = units.find((unit) => unit.id === selectedUnitId) ?? null;
    const usesManualLayout = hasManualLayout(service, units);
    const manualCanvasMinWidth = usesManualLayout
        ? minimumManualCanvasWidth(units, canvasWidth)
        : canvasWidth;

    return (
        <section className="space-y-4">
            <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-300/90">
                        Pilih Unit
                    </p>
                    <h2 className="mt-1 text-2xl font-black tracking-[0.03em] text-white">
                        {service?.name ?? 'Layout booking'}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-zinc-400">
                        Pilih unit langsung dari denah. Setelah submit, unit yang kamu pilih akan di-hold selama 10 menit sambil menunggu konfirmasi staff.
                    </p>
                </div>

                <div className="flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-zinc-300">
                    <span className="rounded-full border border-cyan-300/60 bg-cyan-100/85 px-3 py-1 text-cyan-950">
                        Available
                    </span>
                    <span className="rounded-full border border-emerald-300 bg-emerald-400 px-3 py-1 text-zinc-950">
                        Selected
                    </span>
                    <span className="rounded-full border border-amber-400/60 bg-amber-200/80 px-3 py-1 text-amber-950">
                        Maintenance
                    </span>
                </div>
            </div>

            {units.length === 0 ? (
                <div className="rounded-[2rem] border border-dashed border-white/15 bg-white/[0.03] px-6 py-8 text-sm text-zinc-400">
                    Belum ada unit yang tersedia untuk service ini.
                </div>
            ) : usesManualLayout ? (
                <div className="space-y-4 rounded-[2rem] border border-white/10 bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.18),_transparent_42%),linear-gradient(180deg,_rgba(24,24,27,0.94),_rgba(9,9,11,0.98))] p-4 shadow-[0_24px_80px_-40px_rgba(16,185,129,0.45)]">
                    <div className="flex items-center justify-between gap-3 text-xs text-zinc-400 md:hidden">
                        <p>Geser denah ke samping supaya ukuran unit tetap nyaman disentuh.</p>
                        <span className="rounded-full border border-white/10 bg-white/[0.04] px-3 py-1 font-semibold uppercase tracking-[0.18em] text-zinc-300">
                            Swipe
                        </span>
                    </div>

                    <div
                        className="overflow-x-auto pb-2"
                        role="region"
                        aria-label="Denah unit yang bisa digeser horizontal pada layar kecil"
                        tabIndex={0}
                    >
                        <div
                            className="min-w-full"
                            style={{ minWidth: `${manualCanvasMinWidth}px` }}
                        >
                            <div
                                className="relative overflow-hidden rounded-[1.6rem] border border-white/10 bg-zinc-900/80"
                                style={{ aspectRatio: `${canvasWidth} / ${canvasHeight}` }}
                            >
                                <div
                                    className="absolute inset-0 opacity-70"
                                    style={{
                                        backgroundImage:
                                            'linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px)',
                                        backgroundSize: '48px 48px',
                                    }}
                                />
                                <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,_rgba(16,185,129,0.22),_transparent_26%),radial-gradient(circle_at_80%_30%,_rgba(34,211,238,0.14),_transparent_22%)]" />

                                {units.map((unit) => (
                                    <PositionedUnitButton
                                        key={unit.id}
                                        unit={unit}
                                        canvasWidth={canvasWidth}
                                        canvasHeight={canvasHeight}
                                        selected={selectedUnitId === unit.id}
                                        onSelect={onSelect}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="rounded-[1.5rem] border border-white/10 bg-white/[0.04] px-4 py-3 text-sm text-zinc-300">
                        {selectedUnit ? (
                            <span>
                                Unit terpilih: <span className="font-semibold text-white">{selectedUnit.name}</span> ({selectedUnit.code})
                            </span>
                        ) : (
                            'Klik salah satu unit pada denah untuk memilih slot.'
                        )}
                    </div>
                </div>
            ) : (
                <FallbackUnitGrid units={units} selectedUnitId={selectedUnitId} onSelect={onSelect} />
            )}
        </section>
    );
}
