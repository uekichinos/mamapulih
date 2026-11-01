<script setup lang="ts">
import CustomerAppLayout from '@/Layouts/CustomerAppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SectionCard from '@/Components/SectionCard.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    customer: {
        name: string;
        email: string;
        phone: string | null;
    };
    status?: string;
}>();

const profileForm = useForm({
    name: props.customer.name,
    email: props.customer.email,
    phone: props.customer.phone ?? '',
});

const submitProfile = () =>
    profileForm.patch(route('customer.profile.update'), {
        preserveScroll: true,
    });

const passwordInput = ref<InstanceType<typeof TextInput> | null>(null);
const currentPasswordInput = ref<InstanceType<typeof TextInput> | null>(null);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.put(route('customer.password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
                passwordInput.value?.$el?.focus();
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
                currentPasswordInput.value?.$el?.focus();
            }
        },
    });
};
</script>

<template>
    <CustomerAppLayout>
        <Head title="Profile" />

        <PageHeader title="Profile">Your contact details and password.</PageHeader>

        <div class="space-y-4">
            <SectionCard title="Contact details" tone="indigo">
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <circle cx="12" cy="8" r="3.5" />
                        <path d="M5 20a7 7 0 0 1 14 0" />
                    </svg>
                </template>

                <form class="space-y-4" @submit.prevent="submitProfile">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            v-model="profileForm.name"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError class="mt-2" :message="profileForm.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="profileForm.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError class="mt-2" :message="profileForm.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Phone" />
                        <TextInput
                            id="phone"
                            v-model="profileForm.phone"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="profileForm.errors.phone" />
                    </div>

                    <div class="flex items-center gap-4">
                        <PrimaryButton
                            :class="{ 'opacity-25': profileForm.processing }"
                            :disabled="profileForm.processing"
                        >
                            Save
                        </PrimaryButton>
                        <p
                            v-if="profileForm.recentlySuccessful"
                            class="text-sm text-gray-600 dark:text-gray-400"
                        >
                            Saved.
                        </p>
                    </div>
                </form>
            </SectionCard>

            <SectionCard title="Password" tone="amber">
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <rect x="5" y="11" width="14" height="9" rx="1.5" />
                        <path d="M8 11V8a4 4 0 0 1 8 0v3" />
                    </svg>
                </template>

                <form class="space-y-4" @submit.prevent="submitPassword">
                    <div>
                        <InputLabel for="current_password" value="Current password" />
                        <TextInput
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="passwordForm.current_password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                        />
                        <InputError class="mt-2" :message="passwordForm.errors.current_password" />
                    </div>

                    <div>
                        <InputLabel for="password" value="New password" />
                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="passwordForm.password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="passwordForm.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirm new password" />
                        <TextInput
                            id="password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                        />
                        <InputError
                            class="mt-2"
                            :message="passwordForm.errors.password_confirmation"
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <PrimaryButton
                            :class="{ 'opacity-25': passwordForm.processing }"
                            :disabled="passwordForm.processing"
                        >
                            Update password
                        </PrimaryButton>
                        <p
                            v-if="passwordForm.recentlySuccessful"
                            class="text-sm text-gray-600 dark:text-gray-400"
                        >
                            Saved.
                        </p>
                    </div>
                </form>
            </SectionCard>
        </div>
    </CustomerAppLayout>
</template>
