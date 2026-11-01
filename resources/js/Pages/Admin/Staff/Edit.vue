<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    staff: {
        id: number;
        name: string;
        email: string;
        role: 'owner' | 'kitchen';
    };
}>();

const form = useForm({
    name: props.staff.name,
    email: props.staff.email,
    role: props.staff.role,
    password: '',
});

const submit = () => form.put(route('admin.staff.update', props.staff.id));
</script>

<template>
    <AdminLayout>
        <Head title="Edit staff account" />

        <PageHeader title="Edit staff account">{{ staff.name }}</PageHeader>

        <form @submit.prevent="submit">
            <div
                class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
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
                    <InputLabel for="role" value="Role" />
                    <SelectInput id="role" v-model="form.role" class="mt-1">
                        <option value="kitchen">Kitchen</option>
                        <option value="owner">Owner</option>
                    </SelectInput>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <div>
                    <InputLabel for="password" value="New password" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        placeholder="Leave blank to keep the current password"
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
            </div>

            <div class="mt-4 flex items-center gap-4">
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
