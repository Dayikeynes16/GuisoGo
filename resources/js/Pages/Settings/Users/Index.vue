<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingsLayout from '@/Components/SettingsLayout.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'

const props = defineProps({
    users: Array,
    operator_count: Number,
    max_operators: Number,
})

const canAddMore = computed(() => props.operator_count < props.max_operators)

const deletingUser = ref(null)

function confirmDelete(user) {
    deletingUser.value = user
}

function onConfirmDelete() {
    router.delete(route('settings.users.destroy', deletingUser.value.id))
    deletingUser.value = null
}

const deleteMessage = computed(() =>
    deletingUser.value ? `El usuario "${deletingUser.value.name}" sera eliminado permanentemente.` : '',
)
</script>

<template>
    <Head title="Usuarios" />
    <AppLayout title="Configuracion">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Configuracion</h1>
            <p class="mt-1 text-sm text-gray-500">Gestiona la informacion y preferencias de tu restaurante.</p>
        </div>

        <SettingsLayout>
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Usuarios</h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ operator_count }} de {{ max_operators }} usuarios adicionales
                    </p>
                </div>
                <Link
                    v-if="canAddMore"
                    :href="route('settings.users.create')"
                    class="inline-flex items-center gap-2 bg-[#FF5722] hover:bg-[#D84315] text-white font-semibold rounded-xl px-4 py-2.5 text-sm transition-colors"
                >
                    <span class="material-symbols-outlined text-lg">person_add</span>
                    Agregar usuario
                </Link>
                <span v-else class="text-xs text-gray-400 bg-gray-100 px-3 py-1.5 rounded-full">Limite alcanzado</span>
            </div>

            <!-- Progress bar -->
            <div class="mb-6">
                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all"
                        :class="operator_count >= max_operators ? 'bg-red-500' : 'bg-[#FF5722]'"
                        :style="{ width: Math.min(100, (operator_count / Math.max(1, max_operators)) * 100) + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Users list -->
            <div class="space-y-3">
                <div
                    v-for="u in users"
                    :key="u.id"
                    class="bg-white border border-gray-100 rounded-xl p-4 flex items-center gap-4"
                >
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                        :class="u.role === 'admin' ? 'bg-[#FF5722]/10 text-[#FF5722]' : 'bg-blue-50 text-blue-600'"
                    >
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings:'FILL' 1">
                            {{ u.role === 'admin' ? 'admin_panel_settings' : 'person' }}
                        </span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-gray-900">{{ u.name }}</p>
                            <span
                                class="text-xs px-2 py-0.5 rounded-full font-medium"
                                :class="u.role === 'admin' ? 'bg-[#FF5722]/10 text-[#FF5722]' : 'bg-blue-50 text-blue-600'"
                            >
                                {{ u.role === 'admin' ? 'Administrador' : 'Operador' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400">{{ u.email }}</p>
                        <div v-if="u.branches && u.branches.length > 0" class="flex flex-wrap gap-1 mt-1.5">
                            <span
                                v-for="branch in u.branches"
                                :key="branch.id"
                                class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"
                            >{{ branch.name }}</span>
                        </div>
                    </div>

                    <div v-if="u.role === 'operator'" class="flex items-center gap-1 shrink-0">
                        <Link
                            :href="route('settings.users.edit', u.id)"
                            class="p-2 text-gray-400 hover:text-[#FF5722] hover:bg-orange-50 rounded-xl transition-colors"
                        >
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </Link>
                        <button
                            @click="confirmDelete(u)"
                            class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors"
                        >
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    </div>
                    <div v-else class="shrink-0">
                        <span class="text-xs text-gray-300">Principal</span>
                    </div>
                </div>
            </div>

            <ConfirmModal
                :show="!!deletingUser"
                title="Eliminar usuario"
                :message="deleteMessage"
                confirm-label="Eliminar"
                @confirm="onConfirmDelete"
                @cancel="deletingUser = null"
            />
        </SettingsLayout>
    </AppLayout>
</template>
