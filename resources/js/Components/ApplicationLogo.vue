<script setup lang="ts">
defineProps<{
    white?: boolean;
}>();
</script>

<template>
    <img
        v-if="white"
        src="https://axiomatic.co.za/wp-content/uploads/2022/02/axiomatic_logowhite-300x158.png"
        alt="Axiomatic Consultants"
        class="h-10 w-auto object-contain"
    />
    <img
        v-else
        src="https://axiomatic.co.za/wp-content/uploads/2022/02/axiomatic_logo-150x150.png"
        alt="Axiomatic Consultants"
        class="h-10 w-auto object-contain"
    />
</template>
