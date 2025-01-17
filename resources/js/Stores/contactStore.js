import { ref } from 'vue'

// Initialiser avec la valeur des props
export const contactCount = ref(0)

export function updateContactCount(count) {
    contactCount.value = count
}

// Fonction d'initialisation à appeler depuis le layout
export function initializeContactCount(initialCount) {
    contactCount.value = initialCount
}
