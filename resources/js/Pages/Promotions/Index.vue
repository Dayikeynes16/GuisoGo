<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import ToggleSwitch from '@/Components/ToggleSwitch.vue'

const props = defineProps({
    promotions: Array,
})

const localPromotions = ref([...props.promotions])

// Watch for props changes (after Inertia navigation)
watch(() => props.promotions, (val) => {
    localPromotions.value = [...val]
}, { deep: true })

let dragIndex = null

function onDragStart(index) {
    dragIndex = index
}

function onDragOver(event, index) {
    event.preventDefault()
    if (dragIndex === null || dragIndex === index) { return }
    const items = [...localPromotions.value]
    const [moved] = items.splice(dragIndex, 1)
    items.splice(index, 0, moved)
    localPromotions.value = items
    dragIndex = index
}

function onDragEnd() {
    dragIndex = null
    const ids = localPromotions.value.map((p) => p.id)
    router.patch(route('promotions.reorder'), { ids }, { preserveScroll: true })
}

const totalPromotions = computed(() => props.promotions.length)
const activeNow = computed(() => props.promotions.filter((p) => isVigente(p)).length)
const scheduled = computed(() => props.promotions.filter((p) => p.is_active && !isVigente(p)).length)

const DAY_LABELS = ['D', 'L', 'M', 'Mi', 'J', 'V', 'S']

function isVigente(promo) {
    if (!promo.is_active) { return false }

    const now = new Date()
    const todayIndex = now.getDay()

    if (!promo.active_days || !promo.active_days.includes(todayIndex)) {
        return false
    }

    if (promo.starts_at && promo.ends_at) {
        const currentTime = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false })
        if (currentTime < promo.starts_at || currentTime > promo.ends_at) {
            return false
        }
    }

    return true
}

function statusBadge(promo) {
    if (isVigente(promo)) {
        return { label: 'Vigente', classes: 'bg-green-50 text-green-700' }
    }
    if (promo.is_active) {
        return { label: 'Activa', classes: 'bg-blue-50 text-blue-700' }
    }
    return { label: 'Inactiva', classes: 'bg-gray-100 text-gray-500' }
}

function formatPrice(value) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value)
}

function formatTimeRange(promo) {
    if (!promo.starts_at && !promo.ends_at) { return 'Todo el dia' }
    return `${promo.starts_at || '00:00'} - ${promo.ends_at || '23:59'}`
}

function togglePromotion(promo) {
    router.patch(route('promotions.toggle', promo.id))
}

const deletingPromotion = ref(null)

function deletePromotion(promo) {
    deletingPromotion.value = promo
}

function onConfirmDelete() {
    router.delete(route('promotions.destroy', deletingPromotion.value.id))
    deletingPromotion.value = null
}

function onCancelDelete() {
    deletingPromotion.value = null
}

const deleteMessage = computed(() =>
    deletingPromotion.value ? `La promocion "${deletingPromotion.value.name}" se eliminara permanentemente.` : '',
)
</script>

<template>
    <Head title="Promociones" />
    <AppLayout title="Promociones">

        <!-- Header -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Promociones</h1>
                <p class="mt-1 text-sm text-gray-500">Gestiona las promociones de tu restaurante.</p>
            </div>
            <Link
                :href="route('promotions.create')"
                class="flex items-center gap-2 bg-[#FF5722] hover:bg-[#D84315] text-white font-semibold rounded-xl px-4 py-2.5 text-sm transition-colors"
            >
                <span class="material-symbols-outlined text-lg">add</span>
                Nueva promocion
            </Link>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Total</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ totalPromotions }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Activas Ahora</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ activeNow }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Programadas</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ scheduled }}</p>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="promotions.length === 0" class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
            <span class="material-symbols-outlined text-4xl text-gray-300 mb-3" style="font-variation-settings:'FILL' 1">sell</span>
            <p class="text-gray-500 font-medium mb-1">No hay promociones aun</p>
            <p class="text-sm text-gray-400 mb-4">Crea tu primera promocion.</p>
            <Link
                :href="route('promotions.create')"
                class="bg-[#FF5722] hover:bg-[#D84315] text-white font-semibold rounded-xl px-5 py-2.5 text-sm transition-colors"
            >
                + Nueva promocion
            </Link>
        </div>

        <!-- Ordering hint -->
        <div v-if="localPromotions.length > 0" class="flex items-center gap-2 mb-4 px-4 py-2.5 bg-orange-50/60 border border-orange-100 rounded-xl">
            <span class="material-symbols-outlined text-[#FF5722] text-lg shrink-0" aria-hidden="true">swap_vert</span>
            <p class="text-sm text-gray-600">Arrastra las promociones para cambiar el orden en que apareceran en el menu de tus clientes.</p>
        </div>

        <!-- Promotions table -->
        <div v-if="localPromotions.length > 0" class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="w-16 px-4 py-3"></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Promocion</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Precio</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Dias</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Horario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(promo, index) in localPromotions"
                        :key="promo.id"
                        draggable="true"
                        @dragstart="onDragStart(index)"
                        @dragover="onDragOver($event, index)"
                        @dragend="onDragEnd"
                        class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors cursor-move"
                    >
                        <!-- Drag handle + number -->
                        <td class="px-4 py-4 w-16">
                            <div class="flex items-center gap-1.5 cursor-grab active:cursor-grabbing select-none" aria-label="Arrastra para reordenar">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-gray-100 text-xs font-semibold text-gray-500 tabular-nums">{{ index + 1 }}</span>
                                <span class="material-symbols-outlined text-gray-300 hover:text-gray-400 transition-colors" aria-hidden="true">drag_indicator</span>
                            </div>
                        </td>

                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="promo.image_url"
                                    :src="promo.image_url"
                                    class="w-10 h-10 rounded-xl object-cover shrink-0"
                                />
                                <div v-else class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[#FF5722]" style="font-variation-settings:'FILL' 1">sell</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ promo.name }}</p>
                                    <p v-if="promo.description" class="text-xs text-gray-400 truncate max-w-xs">{{ promo.description }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-orange-50 text-[#FF5722]">
                                {{ formatPrice(promo.price) }}
                            </span>
                        </td>

                        <td class="px-4 py-4">
                            <div class="flex items-center gap-0.5">
                                <span
                                    v-for="(label, index) in DAY_LABELS"
                                    :key="index"
                                    class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium"
                                    :class="promo.active_days && promo.active_days.includes(index)
                                        ? 'bg-[#FF5722] text-white'
                                        : 'bg-gray-100 text-gray-400'"
                                >
                                    {{ label }}
                                </span>
                            </div>
                        </td>

                        <td class="px-4 py-4">
                            <span class="text-sm text-gray-600">{{ formatTimeRange(promo) }}</span>
                        </td>

                        <td class="px-4 py-4">
                            <span
                                class="text-xs font-medium px-2.5 py-1 rounded-full"
                                :class="statusBadge(promo).classes"
                            >
                                {{ statusBadge(promo).label }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <ToggleSwitch
                                    :model-value="promo.is_active"
                                    @update:model-value="togglePromotion(promo)"
                                />
                                <Link
                                    :href="route('promotions.edit', promo.id)"
                                    class="p-2 text-gray-400 hover:text-[#FF5722] hover:bg-orange-50 rounded-xl transition-colors"
                                    title="Editar"
                                >
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </Link>
                                <button
                                    @click="deletePromotion(promo)"
                                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors"
                                    title="Eliminar"
                                >
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmModal
            :show="!!deletingPromotion"
            title="Eliminar promocion"
            :message="deleteMessage"
            confirm-label="Eliminar"
            @confirm="onConfirmDelete"
            @cancel="onCancelDelete"
        />

    </AppLayout>
</template>
