<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SectionCard from '@/Components/SectionCard.vue';
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
    dietTags: { value: string; label: string }[];
}>();

const form = useForm({
    customer: { name: '', email: '', phone: '' },
    recipient_name: '',
    variant_id: null as number | null,
    expected_birth_type: 'normal',
    is_twins: false,
    edd: '',
    address: { line1: '', line2: '', postcode: '', notes: '' },
    diet: { allergies: [] as string[], exclusions: [] as string[], notes: '' },
});

const selectVariant = (variant: Variant) => {
    form.variant_id = variant.id;
    form.expected_birth_type = variant.birth_type;
};

const money = (sen: number) => (sen / 100).toFixed(2);

const submit = () => form.post(route('admin.bookings.store'));
</script>

<template>
    <AdminLayout>
        <Head title="Create booking" />

        <PageHeader title="Create booking">
            Book on behalf of a customer — an existing email reuses that customer's account.
        </PageHeader>

        <form class="space-y-4" @submit.prevent="submit">
            <SectionCard title="Customer" tone="indigo">
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

                <div class="mt-4">
                    <InputLabel for="recipient-name" value="Recipient name (if different)" />
                    <TextInput
                        id="recipient-name"
                        v-model="form.recipient_name"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.recipient_name" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Who the package is actually for, if not the customer themselves — e.g.
                        booking on behalf of a wife. Leave blank to use the customer's own name.
                    </p>
                </div>
            </SectionCard>

            <SectionCard title="Package & variant" tone="blue">
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <line x1="12" y1="3" x2="12" y2="21" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                    </svg>
                </template>

                <div
                    v-for="pkg in packages"
                    :key="pkg.id"
                    class="mb-3 rounded-xl border border-gray-200 p-4 last:mb-0 dark:border-gray-700"
                >
                    <h3 class="mb-2 text-sm font-bold text-gray-900 dark:text-gray-50">
                        {{ pkg.name.en }}
                    </h3>
                    <label
                        v-for="variant in pkg.variants"
                        :key="variant.id"
                        class="mb-2 flex items-center justify-between rounded-lg border p-3 last:mb-0"
                        :class="
                            form.variant_id === variant.id
                                ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30'
                                : 'border-gray-200 dark:border-gray-700'
                        "
                    >
                        <span
                            class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300"
                        >
                            <input
                                type="radio"
                                :checked="form.variant_id === variant.id"
                                @change="selectVariant(variant)"
                            />
                            {{ variant.birth_type === 'csec' ? 'C-sec' : 'Normal' }}
                            · {{ variant.duration_days }} days ·
                            {{
                                variant.meal_plan === 'lunch_dinner'
                                    ? 'Lunch + Dinner'
                                    : 'Lunch only'
                            }}
                        </span>
                        <span
                            class="font-mono text-sm font-semibold text-gray-900 dark:text-gray-100"
                            >RM {{ money(variant.price_sen) }}</span
                        >
                    </label>
                </div>
                <InputError class="mt-1" :message="form.errors.variant_id" />

                <label
                    class="mt-3 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"
                >
                    <input
                        v-model="form.is_twins"
                        type="checkbox"
                        class="rounded border-gray-300"
                    />
                    Twins
                </label>

                <div class="mt-4 max-w-xs">
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
            </SectionCard>

            <SectionCard title="Delivery" tone="green">
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M12 21s-7-5.4-7-11a7 7 0 0 1 14 0c0 5.6-7 11-7 11Z" />
                        <circle cx="12" cy="10" r="2.3" />
                    </svg>
                </template>

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
            </SectionCard>

            <SectionCard title="Diet profile" tone="amber">
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M7 7h.01M12 3a9 9 0 1 0 9 9 9 9 0 0 0-9-9Z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                </template>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Allergies" />
                        <div class="mt-2 flex flex-wrap gap-2">
                            <label
                                v-for="tag in dietTags"
                                :key="tag.value"
                                class="flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-sm text-gray-700 has-checked:border-red-200 has-checked:bg-red-50 has-checked:text-red-700 dark:border-gray-700 dark:text-gray-300 dark:has-checked:border-red-900 dark:has-checked:bg-red-950 dark:has-checked:text-red-300"
                            >
                                <input
                                    v-model="form.diet.allergies"
                                    type="checkbox"
                                    :value="tag.value"
                                    class="rounded border-gray-300"
                                />
                                {{ tag.label }}
                            </label>
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Exclusions" />
                        <div class="mt-2 flex flex-wrap gap-2">
                            <label
                                v-for="tag in dietTags"
                                :key="tag.value"
                                class="flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-sm text-gray-700 has-checked:border-amber-200 has-checked:bg-amber-50 has-checked:text-amber-700 dark:border-gray-700 dark:text-gray-300 dark:has-checked:border-amber-900 dark:has-checked:bg-amber-950 dark:has-checked:text-amber-300"
                            >
                                <input
                                    v-model="form.diet.exclusions"
                                    type="checkbox"
                                    :value="tag.value"
                                    class="rounded border-gray-300"
                                />
                                {{ tag.label }}
                            </label>
                        </div>
                    </div>
                </div>
            </SectionCard>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Create booking
            </PrimaryButton>
        </form>
    </AdminLayout>
</template>
