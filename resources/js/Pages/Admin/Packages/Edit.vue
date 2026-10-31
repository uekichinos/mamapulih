<script setup lang="ts">
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

interface Variant {
    id: number;
    birth_type: 'normal' | 'csec';
    duration_days: number;
    meal_plan: 'lunch' | 'lunch_dinner';
    meals_per_day: number;
    price_sen: number;
    active: boolean;
}

const props = defineProps<{
    package: {
        id: number;
        name: { en: string; ms: string };
        description: { en: string | null; ms: string | null } | null;
        active: boolean;
    };
    variants: Variant[];
}>();

const form = useForm({
    name: { ...props.package.name },
    description: {
        en: props.package.description?.en ?? '',
        ms: props.package.description?.ms ?? '',
    },
    active: props.package.active,
});

const submit = () => {
    // BM input is hidden from the UI; mirror the English value so the
    // (unchanged) backend validation, which still requires both locales,
    // keeps passing.
    form.name.ms = form.name.en;
    form.description.ms = form.description.en;
    form.put(route('admin.packages.update', props.package.id));
};

const showVariantForm = ref(false);

const variantForm = useForm({
    birth_type: 'normal' as 'normal' | 'csec',
    duration_days: 14,
    meal_plan: 'lunch_dinner' as 'lunch' | 'lunch_dinner',
    meals_per_day: 2,
    price_sen: 0,
    active: true,
});

const addVariant = () => {
    variantForm.post(route('admin.packages.variants.store', props.package.id), {
        onSuccess: () => {
            variantForm.reset();
            showVariantForm.value = false;
        },
    });
};

const deleteVariant = (variantId: number) => {
    if (confirm('Delete this variant?')) {
        router.delete(route('admin.variants.destroy', variantId));
    }
};

const formatPrice = (sen: number) => `RM${(sen / 100).toFixed(2)}`;
</script>

<template>
    <AdminLayout>
        <Head title="Edit package" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            Edit package
        </h1>

        <form class="mb-10 max-w-xl space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="name_en" value="Name" />
                <TextInput id="name_en" v-model="form.name.en" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors['name.en']" />
            </div>

            <label class="flex items-center gap-2">
                <input v-model="form.active" type="checkbox" class="rounded border-gray-300" />
                <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>

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

        <div class="max-w-2xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                    Variants
                </h2>
                <SecondaryButton @click="showVariantForm = !showVariantForm">
                    {{ showVariantForm ? 'Cancel' : 'Add variant' }}
                </SecondaryButton>
            </div>

            <form
                v-if="showVariantForm"
                class="mb-6 space-y-4 rounded-md border border-gray-200 p-4 dark:border-gray-700"
                @submit.prevent="addVariant"
            >
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="birth_type" value="Birth type" />
                        <SelectInput id="birth_type" v-model="variantForm.birth_type" class="mt-1">
                            <option value="normal">Normal</option>
                            <option value="csec">Caesarean</option>
                        </SelectInput>
                    </div>

                    <div>
                        <InputLabel for="duration_days" value="Duration (days)" />
                        <SelectInput
                            id="duration_days"
                            v-model="variantForm.duration_days"
                            class="mt-1"
                        >
                            <option :value="7">7</option>
                            <option :value="14">14</option>
                            <option :value="21">21</option>
                            <option :value="28">28</option>
                        </SelectInput>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <InputLabel for="meal_plan" value="Meal plan" />
                        <SelectInput id="meal_plan" v-model="variantForm.meal_plan" class="mt-1">
                            <option value="lunch">Lunch only</option>
                            <option value="lunch_dinner">Lunch + Dinner</option>
                        </SelectInput>
                    </div>

                    <div>
                        <InputLabel for="meals_per_day" value="Meals / day" />
                        <TextInput
                            id="meals_per_day"
                            v-model.number="variantForm.meals_per_day"
                            type="number"
                            class="mt-1 block w-full"
                        />
                    </div>

                    <div>
                        <InputLabel for="price_sen" value="Price (RM)" />
                        <TextInput
                            id="price_sen"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                            :model-value="variantForm.price_sen / 100"
                            @update:model-value="
                                (v) => (variantForm.price_sen = Math.round(Number(v) * 100))
                            "
                        />
                    </div>
                </div>

                <InputError :message="variantForm.errors.price_sen" />

                <PrimaryButton
                    :class="{ 'opacity-25': variantForm.processing }"
                    :disabled="variantForm.processing"
                >
                    Add variant
                </PrimaryButton>
            </form>

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-gray-100 text-xs font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-400"
                    >
                        <tr>
                            <th class="px-4 py-2">Birth type</th>
                            <th class="px-4 py-2">Days</th>
                            <th class="px-4 py-2">Meals</th>
                            <th class="px-4 py-2">Price</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="variant in variants" :key="variant.id">
                            <td class="px-4 py-2 capitalize text-gray-900 dark:text-gray-100">
                                {{ variant.birth_type === 'csec' ? 'Caesarean' : 'Normal' }}
                            </td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                {{ variant.duration_days }}
                            </td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                {{ variant.meal_plan === 'lunch' ? 'Lunch' : 'Lunch + Dinner' }}
                            </td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                {{ formatPrice(variant.price_sen) }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                <DangerButton @click="deleteVariant(variant.id)"
                                    >Delete</DangerButton
                                >
                            </td>
                        </tr>
                        <tr v-if="variants.length === 0">
                            <td colspan="5" class="px-4 py-6 text-center text-gray-600">
                                No variants yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
