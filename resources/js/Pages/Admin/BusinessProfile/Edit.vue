<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    profile: {
        name: string | null;
        contact_phone: string | null;
        email: string | null;
        address: string | null;
        timezone: string;
        currency: string;
        default_locale: string;
    };
}>();

const form = useForm({
    name: props.profile.name ?? '',
    contact_phone: props.profile.contact_phone ?? '',
    email: props.profile.email ?? '',
    address: props.profile.address ?? '',
    timezone: props.profile.timezone,
    currency: props.profile.currency,
    default_locale: props.profile.default_locale,
});

const submit = () => form.put(route('admin.business-profile.update'));
</script>

<template>
    <AdminLayout>
        <Head title="Business profile" />

        <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Business profile
        </h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Business name" />
                <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Contact email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="contact_phone" value="Contact phone" />
                <TextInput
                    id="contact_phone"
                    v-model="form.contact_phone"
                    class="mt-1 block w-full"
                />
                <InputError class="mt-2" :message="form.errors.contact_phone" />
            </div>

            <div>
                <InputLabel for="address" value="Address" />
                <textarea
                    id="address"
                    v-model="form.address"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                ></textarea>
                <InputError class="mt-2" :message="form.errors.address" />
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <InputLabel for="timezone" value="Timezone" />
                    <TextInput
                        id="timezone"
                        v-model="form.timezone"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.timezone" />
                </div>

                <div>
                    <InputLabel for="currency" value="Currency" />
                    <TextInput
                        id="currency"
                        v-model="form.currency"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.currency" />
                </div>

                <div>
                    <InputLabel for="default_locale" value="Default language" />
                    <select
                        id="default_locale"
                        v-model="form.default_locale"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="en">English</option>
                        <option value="ms">Bahasa Melayu</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.default_locale" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Save
                </PrimaryButton>
                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">
                    Saved.
                </p>
            </div>
        </form>
    </AdminLayout>
</template>
