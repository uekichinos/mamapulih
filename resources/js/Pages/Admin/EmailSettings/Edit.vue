<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    settings: {
        host: string;
        port: number;
        encryption: 'tls' | 'ssl' | 'none';
        username: string;
        from_address: string;
        from_name: string;
        has_password: boolean;
    } | null;
}>();

const form = useForm({
    host: props.settings?.host ?? '',
    port: props.settings?.port ?? 587,
    encryption: props.settings?.encryption ?? 'tls',
    username: props.settings?.username ?? '',
    password: '',
    from_address: props.settings?.from_address ?? '',
    from_name: props.settings?.from_name ?? '',
});

const submit = () => form.put(route('admin.email-settings.update'));
const sendTest = () => router.post(route('admin.email-settings.test'));
</script>

<template>
    <AdminLayout>
        <Head title="Email settings" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            Email (SMTP) settings
        </h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="host" value="SMTP host" />
                <TextInput id="host" v-model="form.host" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors.host" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="port" value="Port" />
                    <TextInput
                        id="port"
                        v-model.number="form.port"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.port" />
                </div>

                <div>
                    <InputLabel for="encryption" value="Encryption" />
                    <SelectInput id="encryption" v-model="form.encryption" class="mt-1">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="none">None</option>
                    </SelectInput>
                    <InputError class="mt-2" :message="form.errors.encryption" />
                </div>
            </div>

            <div>
                <InputLabel for="username" value="Username" />
                <TextInput
                    id="username"
                    v-model="form.username"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    :placeholder="
                        settings?.has_password ? 'Leave blank to keep the current password' : ''
                    "
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="from_address" value="From address" />
                    <TextInput
                        id="from_address"
                        v-model="form.from_address"
                        type="email"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.from_address" />
                </div>

                <div>
                    <InputLabel for="from_name" value="From name" />
                    <TextInput
                        id="from_name"
                        v-model="form.from_name"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.from_name" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Save
                </PrimaryButton>
                <SecondaryButton type="button" @click="sendTest">Send test email</SecondaryButton>
                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">
                    Saved.
                </p>
            </div>
        </form>
    </AdminLayout>
</template>
