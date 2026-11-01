<script setup lang="ts">
import CustomerAppLayout from '@/Layouts/CustomerAppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    draft: Record<string, unknown>;
    dietTags: { value: string; label: string }[];
    deliverablePostcodes: string[];
}>();

const form = useForm({
    recipient_name: '',
    address: {
        line1: '',
        line2: '',
        postcode: '',
        notes: '',
    },
    diet: {
        allergies: [] as string[],
        exclusions: [] as string[],
        notes: '',
    },
});

const submit = () => form.post(route('customer.booking.details'));

const isPostcodeDeliverable = computed(() =>
    props.deliverablePostcodes.includes(form.address.postcode),
);
</script>

<template>
    <CustomerAppLayout>
        <Head title="Delivery address and diet" />

        <PageHeader title="Delivery address and diet" />

        <form
            class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="recipient_name" value="Recipient name (optional)" />
                <TextInput
                    id="recipient_name"
                    v-model="form.recipient_name"
                    class="mt-1 block w-full"
                />
                <InputError class="mt-2" :message="form.errors.recipient_name" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    If this package is for someone other than yourself — e.g. your wife — add her
                    name so it's easy to tell apart from your other bookings.
                </p>
            </div>

            <div>
                <InputLabel for="postcode" value="Postcode" />
                <SearchableSelect
                    id="postcode"
                    v-model="form.address.postcode"
                    :options="deliverablePostcodes"
                    placeholder="Search a postcode we deliver to"
                    class="mt-1"
                />
                <InputError class="mt-2" :message="form.errors['address.postcode']" />
                <p
                    v-if="form.address.postcode && !isPostcodeDeliverable"
                    class="mt-2 text-sm text-gray-600 dark:text-gray-400"
                >
                    Pick a postcode from the list to continue.
                </p>
            </div>

            <template v-if="isPostcodeDeliverable">
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
                    <InputLabel for="line2" value="Address (line 2, optional)" />
                    <TextInput id="line2" v-model="form.address.line2" class="mt-1 block w-full" />
                </div>

                <div>
                    <InputLabel for="address-notes" value="Delivery notes (optional)" />
                    <TextInput
                        id="address-notes"
                        v-model="form.address.notes"
                        class="mt-1 block w-full"
                    />
                </div>

                <div class="border-t border-gray-200 pt-4 dark:border-gray-800">
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
                    <InputLabel value="Food exclusions / dislikes" />
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

                <div>
                    <InputLabel for="diet-notes" value="Diet notes (optional)" />
                    <TextInput
                        id="diet-notes"
                        v-model="form.diet.notes"
                        class="mt-1 block w-full"
                    />
                </div>

                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Continue
                </PrimaryButton>
            </template>
        </form>
    </CustomerAppLayout>
</template>
