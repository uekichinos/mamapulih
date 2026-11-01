<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SectionCard from '@/Components/SectionCard.vue';
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

        <PageHeader title="Email (SMTP) settings">
            The mail server used to send booking, payment and reminder emails.
        </PageHeader>

        <form class="space-y-4" @submit.prevent="submit">
            <SectionCard
                title="Server"
                description="Credentials for the SMTP server itself."
                tone="indigo"
            >
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <rect x="3" y="4" width="18" height="7" rx="1.5" />
                        <rect x="3" y="13" width="18" height="7" rx="1.5" />
                        <circle cx="7" cy="7.5" r="0.6" fill="currentColor" />
                        <circle cx="7" cy="16.5" r="0.6" fill="currentColor" />
                    </svg>
                </template>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="host" value="SMTP host" />
                        <TextInput
                            id="host"
                            v-model="form.host"
                            class="mt-1 block w-full"
                            required
                        />
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
                                settings?.has_password
                                    ? 'Leave blank to keep the current password'
                                    : ''
                            "
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>
                </div>
            </SectionCard>

            <SectionCard
                title="Sender"
                description="How emails appear in a customer's inbox."
                tone="amber"
            >
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="m4 6 8 7 8-7" />
                    </svg>
                </template>

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
            </SectionCard>

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
