<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-900 via-purple-800 to-black flex items-center justify-center overflow-hidden">
    <Motion
      :initial="{ opacity: 0, y: -60 }"
      :enter="{ opacity: 1, y: 0, transition: { duration: 1000 } }"
      class="text-center space-y-8 px-6"
    >
      <h1 class="text-5xl font-extrabold tracking-wide text-white animate-pulse">
        🎯 ADSENSE REPORTING SYSTEM
      </h1>

      <p class="text-lg text-gray-300 max-w-2xl mx-auto leading-relaxed">
        Pantau performa dan optimasi penghasilan Google AdSense Anda secara real-time dalam satu platform terpadu.
      </p>

      <Motion
        :initial="{ scale: 0 }"
        :enter="{ scale: 1, transition: { type: 'spring', stiffness: 300 } }"
      >
        <Link
          href="/dashboard"
          class="inline-block mt-8 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 transition-all duration-300 rounded-full shadow-xl hover:shadow-indigo-500/60 text-white font-semibold text-lg animate-bounce"
        >
          🚀 Mulai Sekarang
        </Link>
      </Motion>
    </Motion>
  </div>
</template>

<script setup>

import { Link } from '@inertiajs/vue3'
</script>
