<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Variant {
    id: number;
    birth_type: string;
    duration_days: number;
    meal_plan: string;
    price_sen: number;
}

interface Package {
    id: number;
    name: Record<string, string>;
    variants: Variant[];
}

defineProps<{
    packages: Package[];
    addons: { id: number; name: Record<string, string>; price_sen: number }[];
}>();

const form = useForm({
    customer: { name: '', email: '', phone: '' },
    variant_id: null as number | null,
    expected_birth_type: 'normal',
    is_twins: false,
    edd: '',
    address: { line1: '', line2: '', postcode: '', notes: '' },
    diet: { allergies: '', exclusions: '', notes: '' },
});

const selectVariant = (variant: Variant) => {
    form.variant_id = variant.id;
    form.expected_birth_type = variant.birth_type;
};

const money = (sen: number) => (sen / 100).toFixed(2);

const submit = () => {
    form.transform((data) => ({
        ...data,
        diet: {
            ...data.diet,
            allergies: data.diet.allergies
                ? data.diet.allergies.split(',').map((s: string) => s.trim())
                : [],
            exclusions: data.diet.exclusions
                ? data.diet.exclusions.split(',').map((s: string) => s.trim())
                : [],
        },
    })).post(route('admin.bookings.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Create booking" />

        <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">Create booking</h1>

        <form class="max-w-2xl space-y-6" @submit.prevent="submit">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <InputLabel for="customer-name" value="Customer name" />
                    <TextInput
                        id="customer-name"
                        v-model="form.customer.name"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors['customer.name']" />
                </div>
                <div>
                    <InputLabel for="customer-email" value="Email" />
                    <TextInput
                        id="customer-email"
                        v-model="form.customer.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors['customer.email']" />
                </div>
                <div>
                    <InputLabel for="customer-phone" value="Phone" />
                    <TextInput
                        id="customer-phone"
                        v-model="form.customer.phone"
                        class="mt-1 block w-full"
                    />
                </div>
            </div>

            <div
                v-for="pkg in packages"
                :key="pkg.id"
                class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
            >
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">{{ pkg.name.en }}</h2>
                <label
                    v-for="variant in pkg.variants"
                    :key="variant.id"
                    class="mb-2 flex items-center justify-between rounded-md border p-3"
                    :class="
                        form.variant_id === variant.id
                            ? 'border-indigo-500'
                            : 'border-gray-200 dark:border-gray-700'
                    "
                >
                    <span class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                        <input
                            type="radio"
                            :checked="form.variant_id === variant.id"
                            @change="selectVariant(variant)"
                        />
                        {{ variant.birth_type === 'csec' ? 'C-sec' : 'Normal' }}
                        · {{ variant.duration_days }} days ·
                        {{ variant.meal_plan === 'lunch_dinner' ? 'Lunch + Dinner' : 'Lunch only' }}
                    </span>
                    <span class="font-medium text-gray-900 dark:text-gray-100"
                        >RM {{ money(variant.price_sen) }}</span
                    >
                </label>
            </div>
            <InputError :message="form.errors.variant_id" />

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input v-model="form.is_twins" type="checkbox" />
                Twins
            </label>

            <div>
                <InputLabel for="edd" value="Expected delivery date" />
                <TextInput
                    id="edd"
                    v-model="form.edd"
                    type="date"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.edd" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="line1" value="Address" />
                    <TextInput
                        id="line1"
                        v-model="form.address.line1"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors['address.line1']" />
                </div>
                <div>
                    <InputLabel for="postcode" value="Postcode" />
                    <TextInput
                        id="postcode"
                        v-model="form.address.postcode"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors['address.postcode']" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="allergies" value="Allergies (comma separated)" />
                    <TextInput
                        id="allergies"
                        v-model="form.diet.allergies"
                        class="mt-1 block w-full"
                    />
                </div>
                <div>
                    <InputLabel for="exclusions" value="Exclusions (comma separated)" />
                    <TextInput
                        id="exclusions"
                        v-model="form.diet.exclusions"
                        class="mt-1 block w-full"
                    />
                </div>
            </div>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Create booking
            </PrimaryButton>
        </form>
    </AdminLayout>
</template>
