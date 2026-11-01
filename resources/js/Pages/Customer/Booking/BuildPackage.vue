<script setup lang="ts">
import CustomerAppLayout from '@/Layouts/CustomerAppLayout.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SectionCard from '@/Components/SectionCard.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Variant {
    id: number;
    birth_type: string;
    duration_days: number;
    meal_plan: string;
    price_sen: number;
    active: boolean;
}

interface Package {
    id: number;
    name: Record<string, string>;
    variants: Variant[];
}

interface Addon {
    id: number;
    name: Record<string, string>;
    price_sen: number;
    per: string;
}

const props = defineProps<{
    packages: Package[];
    addons: Addon[];
    draft?: { variant_id?: number; is_twins?: boolean } | null;
}>();

const form = useForm({
    variant_id: props.draft?.variant_id ?? null,
    expected_birth_type: 'normal',
    is_twins: props.draft?.is_twins ?? false,
    addons: [] as { addon_id: number; qty: number }[],
});

const selectedVariant = computed(() =>
    props.packages.flatMap((p) => p.variants).find((v) => v.id === form.variant_id),
);

const selectVariant = (variant: Variant) => {
    form.variant_id = variant.id;
    form.expected_birth_type = variant.birth_type;
};

const toggleAddon = (addonId: number, checked: boolean) => {
    if (checked) {
        form.addons.push({ addon_id: addonId, qty: 1 });
    } else {
        form.addons = form.addons.filter((a) => a.addon_id !== addonId);
    }
};

const money = (sen: number) => (sen / 100).toFixed(2);

const submit = () => form.post(route('customer.booking.build'));
</script>

<template>
    <CustomerAppLayout>
        <Head title="Build your package" />

        <PageHeader title="Build your package" />

        <form class="space-y-6" @submit.prevent="submit">
            <SectionCard v-for="pkg in packages" :key="pkg.id" :title="pkg.name.en">
                <label
                    v-for="variant in pkg.variants"
                    :key="variant.id"
                    class="mb-2 flex items-center justify-between rounded-md border p-3"
                    :class="
                        form.variant_id === variant.id
                            ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30'
                            : 'border-gray-200 dark:border-gray-800 dark:bg-gray-900'
                    "
                >
                    <span class="flex items-center gap-3">
                        <input
                            type="radio"
                            :checked="form.variant_id === variant.id"
                            @change="selectVariant(variant)"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ variant.birth_type === 'csec' ? 'C-sec' : 'Normal' }}
                            · {{ variant.duration_days }} days ·
                            {{
                                variant.meal_plan === 'lunch_dinner'
                                    ? 'Lunch + Dinner'
                                    : 'Lunch only'
                            }}
                        </span>
                    </span>
                    <span class="font-medium text-gray-900 dark:text-gray-100"
                        >RM {{ money(variant.price_sen) }}</span
                    >
                </label>
            </SectionCard>
            <InputError :message="form.errors.variant_id" />

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input v-model="form.is_twins" type="checkbox" />
                    We are expecting twins
                </label>
            </div>

            <SectionCard v-if="addons.length" title="Add-ons">
                <label
                    v-for="addon in addons"
                    :key="addon.id"
                    class="mb-2 flex items-center justify-between"
                >
                    <span class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input
                            type="checkbox"
                            @change="
                                toggleAddon(addon.id, ($event.target as HTMLInputElement).checked)
                            "
                        />
                        {{ addon.name.en }}
                    </span>
                    <span class="text-sm text-gray-900 dark:text-gray-100">
                        RM {{ money(addon.price_sen) }} {{ addon.per === 'day' ? '/ day' : '' }}
                    </span>
                </label>
            </SectionCard>

            <div v-if="selectedVariant" class="text-sm text-gray-600 dark:text-gray-400">
                Selected: {{ selectedVariant.duration_days }} days, RM
                {{ money(selectedVariant.price_sen) }}
            </div>

            <PrimaryButton :disabled="form.processing || !form.variant_id">Continue</PrimaryButton>
        </form>
    </CustomerAppLayout>
</template>
