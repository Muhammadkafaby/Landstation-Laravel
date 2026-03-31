import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
}

function unitStatusTone(status, selected) {
    if (selected) {
        return 'border-emerald-300 bg-emerald-400 text-zinc-950 shadow-[0_16px_40px_-18px_rgba(52,211,153,0.8)]';
    }

    switch (status) {
        case 'maintenance':
            return 'border-amber-300/80 bg-amber-200/80 text-amber-950';
        case 'reserved':
        case 'occupied':
            return 'border-rose-400/80 bg-rose-200/85 text-rose-950';
        default:
            return 'border-cyan-300/70 bg-cyan-100/85 text-cyan-950';
    }
}

function EditorUnitButton({ unit, canvasWidth, canvasHeight, selected, onSelect }) {
    const width = unit.layoutW ?? 180;
    const height = unit.layoutH ?? 110;

    return (
        <button
            type="button"
            onPointerDown={(event) => onSelect(event, unit.id)}
            onClick={(event) => {
                event.preventDefault();
            }}
            className={`absolute overflow-hidden rounded-[1.4rem] border px-3 py-2 text-left transition duration-150 ease-out touch-none select-none ${unitStatusTone(unit.status, selected)}`}
            style={{
                left: `${((unit.layoutX ?? 0) / canvasWidth) * 100}%`,
                top: `${((unit.layoutY ?? 0) / canvasHeight) * 100}%`,
                width: `${(width / canvasWidth) * 100}%`,
                height: `${(height / canvasHeight) * 100}%`,
                transform: `rotate(${unit.layoutRotation ?? 0}deg)`,
                zIndex: unit.layoutZIndex ?? 1,
            }}
            aria-pressed={selected}
        >
            <div className="flex h-full flex-col justify-between">
                <div>
                    <p className="text-[10px] font-semibold uppercase tracking-[0.22em] opacity-75">
                        {unit.status}
                    </p>
                    <h4 className="mt-1 text-sm font-black tracking-[0.08em]">{unit.code}</h4>
                </div>
                <p className="line-clamp-2 text-[11px] font-semibold opacity-80">{unit.name}</p>
            </div>
        </button>
    );
}

export default function LayoutEditor({ services, units }) {
    const timedServices = services.filter((service) => service.serviceType === 'timed_unit');
    const initialServiceId = timedServices[0]?.id ?? null;
    const [selectedServiceId, setSelectedServiceId] = useState(initialServiceId);
    const [selectedUnitId, setSelectedUnitId] = useState(null);
    const [draftUnits, setDraftUnits] = useState([]);
    const [dragState, setDragState] = useState(null);
    const stageRef = useRef(null);

    const selectedService = timedServices.find((service) => service.id === selectedServiceId) ?? null;
    const selectedServiceUnits = units.filter((unit) => unit.serviceId === selectedServiceId);
    const selectedUnit = draftUnits.find((unit) => unit.id === selectedUnitId) ?? draftUnits[0] ?? null;

    const {
        data: serviceData,
        setData: setServiceData,
        patch: patchService,
        processing: serviceProcessing,
        errors: serviceErrors,
    } = useForm({
        service_category_id: selectedService?.serviceCategoryId ?? '',
        code: selectedService?.code ?? '',
        name: selectedService?.name ?? '',
        slug: selectedService?.slug ?? '',
        service_type: selectedService?.serviceType ?? '',
        billing_type: selectedService?.billingType ?? '',
        layout_mode: selectedService?.layoutMode ?? 'manual_grid',
        layout_canvas_width: selectedService?.layoutCanvasWidth ?? 960,
        layout_canvas_height: selectedService?.layoutCanvasHeight ?? 640,
        sort_order: selectedService?.sortOrder ?? 0,
        is_active: selectedService?.isActive ?? true,
    });

    const {
        data: unitData,
        setData: setUnitData,
        patch: patchUnit,
        processing: unitProcessing,
        errors: unitErrors,
    } = useForm({
        service_id: selectedUnit?.serviceId ?? '',
        code: selectedUnit?.code ?? '',
        name: selectedUnit?.name ?? '',
        zone: selectedUnit?.zone ?? '',
        status: selectedUnit?.status ?? 'available',
        capacity: selectedUnit?.capacity ?? '',
        layout_x: selectedUnit?.layoutX ?? '',
        layout_y: selectedUnit?.layoutY ?? '',
        layout_w: selectedUnit?.layoutW ?? '',
        layout_h: selectedUnit?.layoutH ?? '',
        layout_rotation: selectedUnit?.layoutRotation ?? '',
        layout_z_index: selectedUnit?.layoutZIndex ?? '',
        is_bookable: selectedUnit?.isBookable ?? true,
        is_active: selectedUnit?.isActive ?? true,
    });

    useEffect(() => {
        if (! selectedServiceId && initialServiceId) {
            setSelectedServiceId(initialServiceId);
        }
    }, [initialServiceId, selectedServiceId]);

    useEffect(() => {
        setDraftUnits(selectedServiceUnits.map((unit) => ({ ...unit })));
        setSelectedUnitId((current) => {
            if (selectedServiceUnits.some((unit) => unit.id === current)) {
                return current;
            }

            return selectedServiceUnits[0]?.id ?? null;
        });
    }, [selectedServiceId, units]);

    useEffect(() => {
        if (! selectedService) {
            return;
        }

        setServiceData((current) => ({
            ...current,
            service_category_id: selectedService.serviceCategoryId,
            code: selectedService.code,
            name: selectedService.name,
            slug: selectedService.slug,
            service_type: selectedService.serviceType,
            billing_type: selectedService.billingType,
            layout_mode: selectedService.layoutMode ?? 'manual_grid',
            layout_canvas_width: selectedService.layoutCanvasWidth ?? 960,
            layout_canvas_height: selectedService.layoutCanvasHeight ?? 640,
            sort_order: selectedService.sortOrder,
            is_active: selectedService.isActive,
        }));
    }, [selectedService, setServiceData]);

    useEffect(() => {
        if (! selectedUnit) {
            return;
        }

        setUnitData((current) => ({
            ...current,
            service_id: selectedUnit.serviceId,
            code: selectedUnit.code,
            name: selectedUnit.name,
            zone: selectedUnit.zone ?? '',
            status: selectedUnit.status,
            capacity: selectedUnit.capacity ?? '',
            layout_x: selectedUnit.layoutX ?? '',
            layout_y: selectedUnit.layoutY ?? '',
            layout_w: selectedUnit.layoutW ?? '',
            layout_h: selectedUnit.layoutH ?? '',
            layout_rotation: selectedUnit.layoutRotation ?? '',
            layout_z_index: selectedUnit.layoutZIndex ?? '',
            is_bookable: selectedUnit.isBookable,
            is_active: selectedUnit.isActive,
        }));
    }, [selectedUnit, setUnitData]);

    useEffect(() => {
        if (! dragState) {
            return undefined;
        }

        const handlePointerMove = (event) => {
            if (! stageRef.current) {
                return;
            }

            const rect = stageRef.current.getBoundingClientRect();
            const canvasWidth = Number(serviceData.layout_canvas_width) || 960;
            const canvasHeight = Number(serviceData.layout_canvas_height) || 640;

            setDraftUnits((current) => current.map((unit) => {
                if (unit.id !== dragState.unitId) {
                    return unit;
                }

                const width = unit.layoutW ?? 180;
                const height = unit.layoutH ?? 110;
                const nextX = clamp(
                    Math.round(((event.clientX - rect.left - dragState.offsetX) / rect.width) * canvasWidth),
                    0,
                    Math.max(0, canvasWidth - width),
                );
                const nextY = clamp(
                    Math.round(((event.clientY - rect.top - dragState.offsetY) / rect.height) * canvasHeight),
                    0,
                    Math.max(0, canvasHeight - height),
                );

                return {
                    ...unit,
                    layoutX: nextX,
                    layoutY: nextY,
                };
            }));
        };

        const handlePointerUp = () => {
            setDragState(null);
        };

        window.addEventListener('pointermove', handlePointerMove);
        window.addEventListener('pointerup', handlePointerUp);

        return () => {
            window.removeEventListener('pointermove', handlePointerMove);
            window.removeEventListener('pointerup', handlePointerUp);
        };
    }, [dragState, serviceData.layout_canvas_height, serviceData.layout_canvas_width]);

    useEffect(() => {
        if (! selectedUnit) {
            return;
        }

        setUnitData((current) => ({
            ...current,
            layout_x: selectedUnit.layoutX ?? '',
            layout_y: selectedUnit.layoutY ?? '',
            layout_w: selectedUnit.layoutW ?? '',
            layout_h: selectedUnit.layoutH ?? '',
            layout_rotation: selectedUnit.layoutRotation ?? '',
            layout_z_index: selectedUnit.layoutZIndex ?? '',
        }));
    }, [selectedUnit, setUnitData]);

    const handleDragStart = (event, unitId) => {
        event.preventDefault();
        setSelectedUnitId(unitId);

        if (! stageRef.current) {
            return;
        }

        const unit = draftUnits.find((item) => item.id === unitId);

        if (! unit) {
            return;
        }

        const rect = stageRef.current.getBoundingClientRect();
        const left = ((unit.layoutX ?? 0) / (Number(serviceData.layout_canvas_width) || 960)) * rect.width;
        const top = ((unit.layoutY ?? 0) / (Number(serviceData.layout_canvas_height) || 640)) * rect.height;

        setDragState({
            unitId,
            offsetX: event.clientX - rect.left - left,
            offsetY: event.clientY - rect.top - top,
        });
    };

    const updateSelectedUnitField = (field, value) => {
        setDraftUnits((current) => current.map((unit) => (
            unit.id === selectedUnitId
                ? { ...unit, [field]: value }
                : unit
        )));
    };

    const saveServiceCanvas = (event) => {
        event.preventDefault();

        if (! selectedService) {
            return;
        }

        patchService(route('management.services.update', selectedService.id), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const saveSelectedUnit = (event) => {
        event.preventDefault();

        if (! selectedUnit) {
            return;
        }

        patchUnit(route('management.service-units.update', selectedUnit.id), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const canvasWidth = Number(serviceData.layout_canvas_width) || 960;
    const canvasHeight = Number(serviceData.layout_canvas_height) || 640;

    if (timedServices.length === 0) {
        return null;
    }

    return (
        <section className="space-y-6 rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
            <div className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                        Layout Editor
                    </p>
                    <h2 className="mt-1 text-2xl font-bold text-white">Denah unit interaktif</h2>
                    <p className="mt-2 max-w-3xl text-sm leading-6 text-zinc-400">
                        Admin bisa memilih service, mengatur ukuran kanvas, lalu drag unit langsung di denah. Setelah posisinya pas, simpan service canvas dan unit yang sedang dipilih.
                    </p>
                </div>

                <div className="w-full max-w-sm">
                    <InputLabel htmlFor="layout-editor-service" value="Service timed-unit" />
                    <select
                        id="layout-editor-service"
                        className="mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={selectedServiceId ?? ''}
                        onChange={(event) => setSelectedServiceId(Number(event.target.value))}
                    >
                        {timedServices.map((service) => (
                            <option key={service.id} value={service.id}>
                                {service.name} ({service.code})
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <div className="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_360px]">
                <div className="space-y-4">
                    <form onSubmit={saveServiceCanvas} className="grid gap-4 rounded-[2rem] border border-white/10 bg-white/[0.04] p-5 md:grid-cols-3">
                        <div>
                            <InputLabel htmlFor="layout-editor-mode" value="Layout mode" />
                            <TextInput
                                id="layout-editor-mode"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={serviceData.layout_mode || ''}
                                onChange={(event) => setServiceData('layout_mode', event.target.value)}
                            />
                            <InputError className="mt-2" message={serviceErrors.layout_mode} />
                        </div>

                        <div>
                            <InputLabel htmlFor="layout-editor-width" value="Canvas width" />
                            <TextInput
                                id="layout-editor-width"
                                type="number"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={serviceData.layout_canvas_width || ''}
                                onChange={(event) => setServiceData('layout_canvas_width', event.target.value === '' ? null : Number(event.target.value))}
                            />
                            <InputError className="mt-2" message={serviceErrors.layout_canvas_width} />
                        </div>

                        <div>
                            <InputLabel htmlFor="layout-editor-height" value="Canvas height" />
                            <TextInput
                                id="layout-editor-height"
                                type="number"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={serviceData.layout_canvas_height || ''}
                                onChange={(event) => setServiceData('layout_canvas_height', event.target.value === '' ? null : Number(event.target.value))}
                            />
                            <InputError className="mt-2" message={serviceErrors.layout_canvas_height} />
                        </div>

                        <div className="md:col-span-3">
                            <PrimaryButton disabled={serviceProcessing}>
                                Simpan kanvas service
                            </PrimaryButton>
                        </div>
                    </form>

                    <div className="rounded-[2rem] border border-white/10 bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.2),_transparent_32%),linear-gradient(180deg,_rgba(24,24,27,0.94),_rgba(9,9,11,0.98))] p-4 shadow-[0_26px_70px_-40px_rgba(16,185,129,0.6)]">
                        <div
                            ref={stageRef}
                            className="relative overflow-hidden rounded-[1.6rem] border border-white/10 bg-zinc-950/80"
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
                            <div className="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,_rgba(16,185,129,0.2),_transparent_28%),radial-gradient(circle_at_80%_28%,_rgba(34,211,238,0.12),_transparent_22%)]" />

                            {draftUnits.map((unit) => (
                                <EditorUnitButton
                                    key={unit.id}
                                    unit={unit}
                                    canvasWidth={canvasWidth}
                                    canvasHeight={canvasHeight}
                                    selected={unit.id === selectedUnitId}
                                    onSelect={handleDragStart}
                                />
                            ))}
                        </div>
                    </div>
                </div>

                <form onSubmit={saveSelectedUnit} className="space-y-4 rounded-[2rem] border border-white/10 bg-white/[0.04] p-5">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-300">
                            Unit Inspector
                        </p>
                        <h3 className="mt-2 text-xl font-bold text-white">
                            {selectedUnit ? `${selectedUnit.name} (${selectedUnit.code})` : 'Belum memilih unit'}
                        </h3>
                        <p className="mt-2 text-sm leading-6 text-zinc-400">
                            Drag unit di kanvas, atau sesuaikan angka di sini untuk fine tuning.
                        </p>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="layout-inspector-x" value="X" />
                            <TextInput
                                id="layout-inspector-x"
                                type="number"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={unitData.layout_x}
                                onChange={(event) => {
                                    const value = event.target.value === '' ? '' : Number(event.target.value);
                                    setUnitData('layout_x', value);
                                    updateSelectedUnitField('layoutX', value);
                                }}
                            />
                            <InputError className="mt-2" message={unitErrors.layout_x} />
                        </div>

                        <div>
                            <InputLabel htmlFor="layout-inspector-y" value="Y" />
                            <TextInput
                                id="layout-inspector-y"
                                type="number"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={unitData.layout_y}
                                onChange={(event) => {
                                    const value = event.target.value === '' ? '' : Number(event.target.value);
                                    setUnitData('layout_y', value);
                                    updateSelectedUnitField('layoutY', value);
                                }}
                            />
                            <InputError className="mt-2" message={unitErrors.layout_y} />
                        </div>

                        <div>
                            <InputLabel htmlFor="layout-inspector-w" value="Width" />
                            <TextInput
                                id="layout-inspector-w"
                                type="number"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={unitData.layout_w}
                                onChange={(event) => {
                                    const value = event.target.value === '' ? '' : Number(event.target.value);
                                    setUnitData('layout_w', value);
                                    updateSelectedUnitField('layoutW', value);
                                }}
                            />
                            <InputError className="mt-2" message={unitErrors.layout_w} />
                        </div>

                        <div>
                            <InputLabel htmlFor="layout-inspector-h" value="Height" />
                            <TextInput
                                id="layout-inspector-h"
                                type="number"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={unitData.layout_h}
                                onChange={(event) => {
                                    const value = event.target.value === '' ? '' : Number(event.target.value);
                                    setUnitData('layout_h', value);
                                    updateSelectedUnitField('layoutH', value);
                                }}
                            />
                            <InputError className="mt-2" message={unitErrors.layout_h} />
                        </div>

                        <div>
                            <InputLabel htmlFor="layout-inspector-rotation" value="Rotation" />
                            <TextInput
                                id="layout-inspector-rotation"
                                type="number"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={unitData.layout_rotation}
                                onChange={(event) => {
                                    const value = event.target.value === '' ? '' : Number(event.target.value);
                                    setUnitData('layout_rotation', value);
                                    updateSelectedUnitField('layoutRotation', value);
                                }}
                            />
                            <InputError className="mt-2" message={unitErrors.layout_rotation} />
                        </div>

                        <div>
                            <InputLabel htmlFor="layout-inspector-z" value="Z-index" />
                            <TextInput
                                id="layout-inspector-z"
                                type="number"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={unitData.layout_z_index}
                                onChange={(event) => {
                                    const value = event.target.value === '' ? '' : Number(event.target.value);
                                    setUnitData('layout_z_index', value);
                                    updateSelectedUnitField('layoutZIndex', value);
                                }}
                            />
                            <InputError className="mt-2" message={unitErrors.layout_z_index} />
                        </div>
                    </div>

                    <PrimaryButton disabled={unitProcessing || ! selectedUnit}>
                        Simpan unit terpilih
                    </PrimaryButton>
                </form>
            </div>
        </section>
    );
}
