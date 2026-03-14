<script setup>
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingsLayout from '@/Components/SettingsLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'

const props = defineProps({
    ranges: Array,
})

const flash = computed(() => usePage().props.flash)

// ─── Add range ──────────────────────────────────────────────────────────────

const addForm = useForm({
    min_km: '',
    max_km: '',
    price: '',
})

const addClientErrors = computed(() => {
    const errors = {}
    const min = parseFloat(addForm.min_km)
    const max = parseFloat(addForm.max_km)
    const price = parseFloat(addForm.price)

    if (addForm.min_km !== '' && (isNaN(min) || min < 0)) {
        errors.min_km = 'Debe ser un numero mayor o igual a 0.'
    }
    if (addForm.max_km !== '' && addForm.min_km !== '') {
        if (isNaN(max)) {
            errors.max_km = 'Debe ser un numero.'
        } else if (!isNaN(min) && max <= min) {
            errors.max_km = '"Hasta" debe ser mayor que "Desde".'
        }
    }
    if (addForm.price !== '' && (isNaN(price) || price < 0)) {
        errors.price = 'Debe ser un numero mayor o igual a $0.'
    }

    // Check overlap against existing ranges
    if (!isNaN(min) && !isNaN(max) && max > min) {
        const overlapping = props.ranges.find(
            (r) => r.min_km < max && r.max_km > min,
        )
        if (overlapping) {
            errors.min_km = `Se superpone con el rango ${overlapping.min_km}–${overlapping.max_km} km.`
        }
    }

    return errors
})

const addHasClientErrors = computed(() => Object.keys(addClientErrors.value).length > 0)

function addRange() {
    if (addHasClientErrors.value) { return }
    addForm.post(route('settings.shipping-rates.store'), {
        onSuccess: () => addForm.reset(),
    })
}

// ─── Inline editing ─────────────────────────────────────────────────────────

const editing = ref(null)
const editForm = useForm({ min_km: '', max_km: '', price: '' })

const editClientErrors = computed(() => {
    if (editing.value === null) { return {} }
    const errors = {}
    const min = parseFloat(editForm.min_km)
    const max = parseFloat(editForm.max_km)
    const price = parseFloat(editForm.price)

    if (editForm.min_km !== '' && (isNaN(min) || min < 0)) {
        errors.min_km = 'Debe ser un numero mayor o igual a 0.'
    }
    if (editForm.max_km !== '' && editForm.min_km !== '') {
        if (isNaN(max)) {
            errors.max_km = 'Debe ser un numero.'
        } else if (!isNaN(min) && max <= min) {
            errors.max_km = '"Hasta" debe ser mayor que "Desde".'
        }
    }
    if (editForm.price !== '' && (isNaN(price) || price < 0)) {
        errors.price = 'Debe ser un numero mayor o igual a $0.'
    }

    // Check overlap excluding current
    if (!isNaN(min) && !isNaN(max) && max > min) {
        const overlapping = props.ranges.find(
            (r) => r.id !== editing.value && r.min_km < max && r.max_km > min,
        )
        if (overlapping) {
            errors.min_km = `Se superpone con el rango ${overlapping.min_km}–${overlapping.max_km} km.`
        }
    }

    return errors
})

const editHasClientErrors = computed(() => Object.keys(editClientErrors.value).length > 0)

function startEdit(range) {
    editing.value = range.id
    editForm.min_km = range.min_km
    editForm.max_km = range.max_km
    editForm.price = range.price
    editForm.clearErrors()
}

function saveEdit(rangeId) {
    if (editHasClientErrors.value) { return }
    editForm.put(route('settings.shipping-rates.update', rangeId), {
        onSuccess: () => { editing.value = null },
    })
}

function cancelEdit() {
    editing.value = null
    editForm.clearErrors()
}

// ─── Delete ─────────────────────────────────────────────────────────────────

const deletingRangeId = ref(null)

function deleteRange(rangeId) {
    deletingRangeId.value = rangeId
}

function onConfirmDeleteRange() {
    router.delete(route('settings.shipping-rates.destroy', deletingRangeId.value))
    deletingRangeId.value = null
}

function onCancelDeleteRange() {
    deletingRangeId.value = null
}

// ─── Helpers ────────────────────────────────────────────────────────────────

function formatPrice(value) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value)
}

const maxCoverage = computed(() => {
    if (!props.ranges.length) { return null }
    return Math.max(...props.ranges.map((r) => r.max_km))
})

function mergedErrors(formObj, clientErrors, field) {
    return clientErrors[field] || formObj.errors[field] || null
}
</script>

<template>
    <Head title="Tarifas de Envío" />
    <AppLayout title="Configuración">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Configuración</h1>
            <p class="mt-1 text-sm text-gray-500">Gestiona la información y preferencias de tu restaurante.</p>
        </div>

        <SettingsLayout>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Tarifas de envio</h2>
                <p class="text-sm text-gray-500 mb-2">
                    Define cuanto cobras por envio segun la distancia entre la sucursal y el cliente.
                </p>

                <!-- How it works hint -->
                <div class="flex items-start gap-2.5 mb-6 px-4 py-3 bg-blue-50/60 border border-blue-100 rounded-xl">
                    <span class="material-symbols-outlined text-blue-500 text-lg mt-0.5 shrink-0" aria-hidden="true">help</span>
                    <div class="text-xs text-blue-700 space-y-1">
                        <p><strong>Como funciona:</strong> Cada rango cubre una zona de distancia. Si el cliente esta a 3 km y tienes un rango de 0–5 km con precio $30, se cobraran $30 de envio.</p>
                        <p>Los rangos no deben superponerse. El rango mas lejano define tu cobertura maxima de entrega<span v-if="maxCoverage"> (actualmente <strong>{{ maxCoverage }} km</strong>)</span>.</p>
                    </div>
                </div>

                <!-- Success flash -->
                <div
                    v-if="flash?.success"
                    class="flex items-center gap-2 mb-4 px-4 py-2.5 bg-green-50 border border-green-100 rounded-xl"
                    aria-live="polite"
                >
                    <span class="material-symbols-outlined text-green-600 text-lg" aria-hidden="true">check_circle</span>
                    <p class="text-sm text-green-700">{{ flash.success }}</p>
                </div>

                <!-- Ranges table -->
                <div class="rounded-xl border border-gray-100 overflow-hidden mb-6">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Desde (km)</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Hasta (km)</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Costo de envio</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="range in ranges" :key="range.id" class="hover:bg-gray-50/50">
                                <!-- Editing row -->
                                <template v-if="editing === range.id">
                                    <td class="px-4 py-3">
                                        <div>
                                            <input
                                                v-model="editForm.min_km"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                aria-label="Desde (km)"
                                                class="w-24 border rounded-lg px-3 py-1.5 text-sm tabular-nums focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5722]/50"
                                                :class="mergedErrors(editForm, editClientErrors, 'min_km') ? 'border-red-300' : 'border-gray-200'"
                                            />
                                            <p v-if="mergedErrors(editForm, editClientErrors, 'min_km')" class="mt-1 text-xs text-red-500 max-w-[12rem]">{{ mergedErrors(editForm, editClientErrors, 'min_km') }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <input
                                                v-model="editForm.max_km"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                aria-label="Hasta (km)"
                                                class="w-24 border rounded-lg px-3 py-1.5 text-sm tabular-nums focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5722]/50"
                                                :class="mergedErrors(editForm, editClientErrors, 'max_km') ? 'border-red-300' : 'border-gray-200'"
                                            />
                                            <p v-if="mergedErrors(editForm, editClientErrors, 'max_km')" class="mt-1 text-xs text-red-500 max-w-[12rem]">{{ mergedErrors(editForm, editClientErrors, 'max_km') }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <input
                                                v-model="editForm.price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                aria-label="Costo de envio"
                                                class="w-24 border rounded-lg px-3 py-1.5 text-sm tabular-nums focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5722]/50"
                                                :class="mergedErrors(editForm, editClientErrors, 'price') ? 'border-red-300' : 'border-gray-200'"
                                            />
                                            <p v-if="mergedErrors(editForm, editClientErrors, 'price')" class="mt-1 text-xs text-red-500 max-w-[12rem]">{{ mergedErrors(editForm, editClientErrors, 'price') }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right align-top">
                                        <div class="flex items-center justify-end gap-2 pt-1">
                                            <button
                                                @click="saveEdit(range.id)"
                                                :disabled="editHasClientErrors"
                                                class="text-xs font-semibold text-[#FF5722] hover:underline disabled:opacity-50 disabled:no-underline"
                                            >
                                                Guardar
                                            </button>
                                            <button @click="cancelEdit" class="text-xs font-semibold text-gray-400 hover:text-gray-600">Cancelar</button>
                                        </div>
                                    </td>
                                </template>
                                <!-- Normal row -->
                                <template v-else>
                                    <td class="px-4 py-3 text-sm text-gray-700 tabular-nums">{{ range.min_km }} km</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 tabular-nums">{{ range.max_km }} km</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 tabular-nums">{{ formatPrice(range.price) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button
                                                @click="startEdit(range)"
                                                class="text-gray-400 hover:text-[#FF5722] transition-colors"
                                                :aria-label="`Editar rango ${range.min_km}–${range.max_km} km`"
                                            >
                                                <span class="material-symbols-outlined text-lg" aria-hidden="true">edit</span>
                                            </button>
                                            <button
                                                @click="deleteRange(range.id)"
                                                class="text-gray-400 hover:text-red-500 transition-colors"
                                                :aria-label="`Eliminar rango ${range.min_km}–${range.max_km} km`"
                                            >
                                                <span class="material-symbols-outlined text-lg" aria-hidden="true">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                            <tr v-if="!ranges.length">
                                <td colspan="4" class="px-4 py-8 text-center">
                                    <span class="material-symbols-outlined text-3xl text-gray-300 mb-2" style="font-variation-settings:'FILL' 1" aria-hidden="true">local_shipping</span>
                                    <p class="text-sm text-gray-500 font-medium">Sin tarifas configuradas</p>
                                    <p class="text-xs text-gray-400 mt-1">Agrega tu primer rango de distancia para habilitar la entrega a domicilio.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Add range form -->
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">Agregar nuevo rango</h3>
                    <p class="text-xs text-gray-400 mb-4">El rango no debe superponerse con los existentes.</p>
                    <form @submit.prevent="addRange" class="flex flex-wrap items-start gap-4">
                        <div class="pb-5">
                            <label for="add-min-km" class="block text-xs font-medium text-gray-500 mb-1">Desde (km)</label>
                            <input
                                id="add-min-km"
                                v-model="addForm.min_km"
                                type="number"
                                step="0.01"
                                min="0"
                                inputmode="decimal"
                                placeholder="0…"
                                class="w-28 border rounded-xl px-3 py-2.5 text-sm tabular-nums focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5722]/50"
                                :class="mergedErrors(addForm, addClientErrors, 'min_km') ? 'border-red-300' : 'border-gray-200'"
                            />
                            <p v-if="mergedErrors(addForm, addClientErrors, 'min_km')" class="mt-1 text-xs text-red-500 max-w-[14rem]">{{ mergedErrors(addForm, addClientErrors, 'min_km') }}</p>
                        </div>
                        <div class="pb-5">
                            <label for="add-max-km" class="block text-xs font-medium text-gray-500 mb-1">Hasta (km)</label>
                            <input
                                id="add-max-km"
                                v-model="addForm.max_km"
                                type="number"
                                step="0.01"
                                min="0"
                                inputmode="decimal"
                                placeholder="5…"
                                class="w-28 border rounded-xl px-3 py-2.5 text-sm tabular-nums focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5722]/50"
                                :class="mergedErrors(addForm, addClientErrors, 'max_km') ? 'border-red-300' : 'border-gray-200'"
                            />
                            <p v-if="mergedErrors(addForm, addClientErrors, 'max_km')" class="mt-1 text-xs text-red-500 max-w-[14rem]">{{ mergedErrors(addForm, addClientErrors, 'max_km') }}</p>
                        </div>
                        <div class="pb-5">
                            <label for="add-price" class="block text-xs font-medium text-gray-500 mb-1">Costo de envio ($)</label>
                            <input
                                id="add-price"
                                v-model="addForm.price"
                                type="number"
                                step="0.01"
                                min="0"
                                inputmode="decimal"
                                placeholder="30…"
                                class="w-28 border rounded-xl px-3 py-2.5 text-sm tabular-nums focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF5722]/50"
                                :class="mergedErrors(addForm, addClientErrors, 'price') ? 'border-red-300' : 'border-gray-200'"
                            />
                            <p v-if="mergedErrors(addForm, addClientErrors, 'price')" class="mt-1 text-xs text-red-500 max-w-[14rem]">{{ mergedErrors(addForm, addClientErrors, 'price') }}</p>
                        </div>
                        <div class="pt-[1.375rem]">
                            <button
                                type="submit"
                                :disabled="addForm.processing || addHasClientErrors"
                                class="flex items-center gap-2 bg-[#FF5722] hover:bg-[#D84315] text-white font-semibold rounded-xl px-5 py-2.5 text-sm transition-colors disabled:opacity-60"
                            >
                                <span class="material-symbols-outlined text-lg" aria-hidden="true">add</span>
                                Agregar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </SettingsLayout>

        <ConfirmModal
            :show="!!deletingRangeId"
            title="¿Eliminar este rango?"
            message="La tarifa de envio para esta zona de distancia se eliminara permanentemente. Si no queda ningun rango, la entrega a domicilio se desactivara."
            confirm-label="Eliminar"
            @confirm="onConfirmDeleteRange"
            @cancel="onCancelDeleteRange"
        />
    </AppLayout>
</template>
