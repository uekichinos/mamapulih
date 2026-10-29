<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    role: 'kitchen' as 'owner' | 'kitchen',
    password: '',
});

const submit = () => form.post(route('admin.staff.store'));
</script>

<template>
    <AdminLayout>
        <Head title="New staff account" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            New staff account
        </h1>

        <form class="max-w-md space-y-4" @submit.prevent="submit">
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
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Create
            </PrimaryButton>
        </form>
    </AdminLayout>
</template>
