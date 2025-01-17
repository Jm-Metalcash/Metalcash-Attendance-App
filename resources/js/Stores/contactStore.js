import { ref } from 'vue'

export const contactCount = ref(0)

export function updateContactCount(count) {
    contactCount.value = count
}
