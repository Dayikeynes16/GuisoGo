<script setup>
import { useForm, Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const flash = computed(() => usePage().props.flash)

const form = useForm({
    email: '',
})

function submit() {
    form.post(route('password.email'))
}
</script>

<template>
    <Head title="Recuperar contrasena" />

    <div class="min-h-screen bg-[#FAFAFA] flex items-center justify-center p-4">
        <div class="w-full max-w-md">

            <!-- Logo -->
            <div class="flex flex-col items-center mb-8">
                <div class="bg-orange-50 p-3 rounded-2xl mb-3">
                    <span class="material-symbols-outlined text-[#FF5722] text-4xl" style="font-variation-settings:'FILL' 1">
                        local_fire_department
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">PideAqui</h1>
                <p class="text-sm text-gray-500 mt-1">Recuperar contrasena</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

                <!-- Success message -->
                <div v-if="flash?.success" class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3 mb-6">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-green-500 text-lg shrink-0">check_circle</span>
                        {{ flash.success }}
                    </div>
                </div>

                <template v-if="!flash?.success">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Olvide mi contrasena</h2>
                    <p class="text-sm text-gray-500 mb-6">Ingresa tu correo y te enviaremos instrucciones para restablecerla.</p>

                    <form @submit.prevent="submit" class="space-y-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Correo electronico
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                autofocus
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FF5722]/30 focus:border-[#FF5722] transition-colors"
                                :class="{ 'border-red-400': form.errors.email }"
                                placeholder="admin@restaurante.com"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-[#FF5722] hover:bg-[#D84315] text-white font-semibold rounded-xl py-2.5 text-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Enviando...' : 'Enviar instrucciones' }}
                        </button>
                    </form>
                </template>

                <template v-else>
                    <p class="text-sm text-gray-600 mb-4">Revisa tu bandeja de entrada y sigue las instrucciones del correo.</p>
                    <button
                        @click="form.reset(); usePage().props.flash.success = null"
                        class="text-sm text-[#FF5722] hover:text-[#D84315] font-medium transition-colors"
                    >
                        Enviar otro correo
                    </button>
                </template>

                <div class="mt-6 text-center">
                    <Link :href="route('login')" class="text-sm text-gray-500 hover:text-gray-700 font-medium transition-colors">
                        Volver al inicio de sesion
                    </Link>
                </div>
            </div>

        </div>
    </div>
</template>
