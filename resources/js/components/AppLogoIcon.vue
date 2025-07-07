<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
  <svg width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
  <!-- Latar belakang -->
  <rect width="120" height="120" rx="20" fill="#F4F4F4"/>
  
  <!-- Elemen biru (melambangkan bagian dari logo AdSense) -->
  <rect x="30" y="30" width="25" height="60" rx="6" fill="#1A73E8"/>

  <!-- Elemen kuning -->
  <path d="M60 30 L90 90 H75 L45 30 Z" fill="#FBBC05"/>

  <!-- Elemen hijau opsional (gaya modern) -->
  <circle cx="90" cy="30" r="10" fill="#34A853"/>
</svg>

</template>
