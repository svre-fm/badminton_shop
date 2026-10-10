<button type="button" x-data="{ on: false }" @click="on = !on" :aria-pressed="on" aria-label="Favorite"
        :class="on ? 'text-heart' : 'text-heart/60'">
    <svg class="size-9" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="currentColor"
         :fill-opacity="on ? 1 : 0.9"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/></svg>
</button>
