<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Header from "@/Components/Header.vue";
import Legend from "@/Components/Legend.vue";
import { Head, usePage, router, Link } from "@inertiajs/vue3";
import { ref, computed, watch } from 'vue';
import { updateContactCount } from '@/Stores/contactStore';

const page = usePage();
const contactsToCall = ref(page.props.contactsToCall);
const callHistory = ref(page.props.callHistory || []); // Ajout de la référence à l'historique
const sortDirection = ref('desc');
const historySort = ref('desc');
const showConfirmDialog = ref(false);
const currentContact = ref(null);
const showSuccessMessage = ref(false);
const tempActionValue = ref(null);
const selectedActions = ref({});
const note = ref('');

const emits = defineEmits(['updateContactCount']);

const toggleSort = () => {
    sortDirection.value = sortDirection.value === 'desc' ? 'asc' : 'desc';
};

const getActionLabel = (value) => {
    const actions = {
        0: 'À contacter',
        1: 'Clôturé',
        2: 'Injoignable',
        3: 'Annuler'
    };
    return actions[value] || '';
};

const confirmAction = (contact, newValue) => {
    selectedActions.value[contact.id] = newValue;
    currentContact.value = contact;
    tempActionValue.value = newValue;
    showConfirmDialog.value = true;
};

const handleCancel = () => {
    tempActionValue.value = null;
    showConfirmDialog.value = false;
    note.value = '';
};

const getContactAction = (contact) => {
    if (!(contact.id in selectedActions.value)) {
        selectedActions.value[contact.id] = '';
    }
    return selectedActions.value[contact.id];
};

const sortedContacts = computed(() => {
    // Filtrer d'abord les contacts selon les critères
    const filteredContacts = contactsToCall.value.filter(contact => {
        const actionValue = parseInt(contact.action_relance);
        return actionValue === 0 || actionValue === 2;
    });

    // Mettre à jour le compteur global
    updateContactCount(filteredContacts.length);

    // Puis trier les contacts filtrés
    return filteredContacts.sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        return sortDirection.value === 'desc' 
            ? dateB - dateA 
            : dateA - dateB;
    });
});

watch(sortedContacts, (newContacts) => {
    emits('updateContactCount', newContacts.length);
}, { immediate: true });

const sortedHistory = computed(() => {
    return [...callHistory.value].sort((a, b) => {
        const dateA = new Date(a.date_relance_modified);
        const dateB = new Date(b.date_relance_modified);
        return historySort.value === 'desc' 
            ? dateB - dateA 
            : dateA - dateB;
    });
});

const handleConfirm = () => {
    if (currentContact.value && tempActionValue.value !== null) {
        const oldStatus = currentContact.value.action_relance;
        currentContact.value.action_relance = tempActionValue.value;
        
        // Ajouter l'entrée dans l'historique local
        callHistory.value.unshift({
            date_relance_modified: new Date().toISOString(),
            contact_name: currentContact.value.name,
            old_status_relance: oldStatus,
            new_status_relance: tempActionValue.value,
            modified_by_relance: page.props.auth.user.name,
            type: currentContact.value.type,
            note: note.value
        });

        router.post('/update-action', {
            id: currentContact.value.id,
            type: currentContact.value.type,
            action: tempActionValue.value,
            old_status: oldStatus,
            note: note.value
        }, {
            preserveScroll: true,
            preserveState: true
        });

        // Réinitialiser la sélection
        selectedActions.value[currentContact.value.id] = '';
        
        showConfirmDialog.value = false;
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 2000);
    }
};
</script>

<template>
    <Head title="Demandes de rappel" />

    <AuthenticatedLayout>
        <template #header>
            <Header :pageTitle="'Demandes de rappel'" />
        </template>

        <!-- Message de succès -->
        <div v-if="showSuccessMessage" 
            class="fixed top-5 right-5 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg z-50 shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>Le statut a été mis à jour avec succès.</span>
            </div>
        </div>

        <!-- Boîte de dialogue de confirmation -->
        <div v-if="showConfirmDialog" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Confirmation</h3>
                <p class="mb-4">
                    Voulez-vous vraiment définir le statut de <span class="font-semibold">{{ currentContact?.name }}</span> 
                    sur <span class="font-semibold">{{ getActionLabel(tempActionValue) }}</span> ?
                </p>
                <div class="mb-4">
                    <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Note (optionnelle)</label>
                    <textarea
                        id="note"
                        rows="3"
                        v-model="note"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#005692] focus:border-[#005692] placeholder-gray-400"
                        placeholder="Ajoutez une note explicative..."
                    ></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button 
                        @click="handleCancel"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                    <button 
                        @click="handleConfirm"
                        class="px-4 py-2 bg-[#005692] text-white rounded-md hover:bg-[rgba(0,85,150,0.8)] transition-colors">
                        Confirmer
                    </button>
                </div>
            </div>
        </div>

        <section class="bg-gray-50 pt-8 pb-16 min-h-screen">
            <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="p-6 rounded-lg text-center w-full bg-white shadow-sm mb-8">
                    <h2 class="text-gray-800 text-xl sm:text-2xl font-semibold py-4">
                        <i class="fa-solid fa-clock-rotate-left text-[#005692] mr-2"></i> Liste de demandes de rappel
                    </h2>
                </div>

                <!-- Vue mobile et tablette : cartes empilées -->
                <div class="lg:hidden mb-8 bg-white rounded-xl shadow-sm p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-700">Contacts à rappeler</h3>
                        <button 
                            @click="toggleSort" 
                            class="flex items-center text-sm text-gray-600 bg-gray-100 px-3 py-1.5 rounded-md hover:bg-gray-200 transition-colors"
                        >
                            <span class="mr-1">Date</span>
                            <i 
                                class="fa-solid"
                                :class="{
                                    'fa-sort-up': sortDirection === 'asc',
                                    'fa-sort-down': sortDirection === 'desc'
                                }"
                            ></i>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div v-for="contact in sortedContacts" :key="contact.id" 
                            class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                            <div class="flex justify-between items-start mb-3">
                                <span 
                                    class="px-2 py-1 rounded-md cursor-pointer hover:opacity-80 inline-block mb-2"
                                    :class="contact.type === 'client' ? 'bg-blue-100 text-blue-800' : 'bg-[#f5e9c4] text-[#60501e]'"
                                    @click="() => {
                                        if (contact.type === 'client') {
                                            router.visit(route('management-call.client', { id: contact.id }));
                                        } else {
                                            router.visit(route('management-call', { prospect: contact.id }));
                                        }
                                    }"
                                >
                                    {{ contact.name }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ new Date(contact.date).toLocaleDateString('fr-FR') }}
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <div class="text-sm text-gray-600 mb-1 font-medium">Téléphone:</div>
                                <div class="text-sm text-gray-600">{{ contact.phone || 'Aucun numéro enregistré' }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="text-sm text-gray-600 mb-1 font-medium">Note:</div>
                                <div v-html="contact.note" class="text-sm text-gray-600 whitespace-pre-wrap"></div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="text-sm text-gray-600 mb-1 font-medium">Statut:</div>
                                <div class="text-sm text-gray-600">{{ getActionLabel(contact.action_relance) }}</div>
                            </div>
                            
                            <div class="mt-3">
                                <select
                                    :value="getContactAction(contact)"
                                    @change="confirmAction(contact, $event.target.value)"
                                    class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-[#005692] focus:border-[#005692] appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20d%3D%22M5.293%207.293a1%201%200%20011.414%200L10%2010.586l3.293-3.293a1%201%200%20111.414%201.414l-4%204a1%201%200%2001-1.414%200l-4-4a1%201%200%20010-1.414z%22%20fill%3D%22%236B7280%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.5em_1.5em] bg-[right_0.3rem_center] bg-no-repeat pr-8"
                                >
                                    <option value="" disabled>Choisir une action</option>
                                    <option value="0">À contacter</option>
                                    <option value="1">Clôturé</option>
                                    <option value="2">Injoignable</option>
                                    <option value="3">Annuler</option>
                                </select>
                            </div>
                        </div>
                        
                        <div v-if="sortedContacts.length === 0" class="p-4 text-center text-sm text-gray-500 bg-white rounded-lg shadow-sm">
                            Aucun contact à rappeler.
                        </div>
                    </div>
                </div>

                <!-- Vue desktop : tableau classique -->
                <div class="hidden lg:block mb-8 bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Contacts à rappeler</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 rounded-xl overflow-hidden">
                            <thead>
                                <tr class="bg-gradient-to-r from-[#005692] to-[#0078c9] text-white">
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">
                                        Nom
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">
                                        Téléphone
                                    </th>
                                    <th 
                                        @click="toggleSort"
                                        class="px-6 py-4 text-left text-sm font-medium tracking-wider cursor-pointer hover:bg-[rgba(0,85,150,0.9)]"
                                    >
                                        Date
                                        <i 
                                            class="fa-solid ml-1"
                                            :class="{
                                                'fa-sort-up': sortDirection === 'asc',
                                                'fa-sort-down': sortDirection === 'desc'
                                            }"
                                        ></i>
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">
                                        Note
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">
                                        Statut
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="contact in sortedContacts" :key="contact.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            class="px-2 py-1 rounded-md cursor-pointer hover:opacity-80"
                                            :class="contact.type === 'client' ? 'bg-blue-100 text-blue-800' : 'bg-[#f5e9c4] text-[#60501e]'"
                                            @click="() => {
                                                if (contact.type === 'client') {
                                                    router.visit(route('management-call.client', { id: contact.id }));
                                                } else {
                                                    router.visit(route('management-call', { prospect: contact.id }));
                                                }
                                            }"
                                        >
                                            {{ contact.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ contact.phone || 'Aucun numéro enregistré' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ new Date(contact.date).toLocaleDateString('fr-FR') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div v-html="contact.note" class="whitespace-pre-wrap"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ getActionLabel(contact.action_relance) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select
                                            :value="getContactAction(contact)"
                                            @change="confirmAction(contact, $event.target.value)"
                                            class="block w-full px-2 py-1 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-[#005692] focus:border-[#005692] appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20d%3D%22M5.293%207.293a1%201%200%20011.414%200L10%2010.586l3.293-3.293a1%201%200%20111.414%201.414l-4%204a1%201%200%2001-1.414%200l-4-4a1%201%200%20010-1.414z%22%20fill%3D%22%236B7280%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.5em_1.5em] bg-[right_0.3rem_center] bg-no-repeat pr-8"
                                        >
                                            <option value="" disabled>Choisir une action</option>
                                            <option value="0">À contacter</option>
                                            <option value="1">Clôturé</option>
                                            <option value="2">Injoignable</option>
                                            <option value="3">Annuler</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr v-if="sortedContacts.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Aucun contact à rappeler.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Légende -->
                <Legend />

                <!-- Historique des 7 derniers jours - Vue mobile et tablette -->
                <div class="lg:hidden mt-8 bg-white rounded-xl shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">
                        <i class="fas fa-history text-[#005692] mr-2"></i>
                        Historique des modifications
                    </h3>
                    
                    <div class="space-y-4">
                        <div v-for="(history, index) in sortedHistory" :key="index" 
                            class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                            <div class="flex justify-between items-start mb-3">
                                <span 
                                    class="px-2 py-1 rounded-md cursor-pointer hover:opacity-80 inline-block mb-2"
                                    :class="history.type === 'client' ? 'bg-blue-100 text-blue-800' : 'bg-[#f5e9c4] text-[#60501e]'"
                                    @click="() => {
                                        if (history.type === 'client') {
                                            router.visit(route('management-call.client', { id: history.id }));
                                        } else {
                                            router.visit(route('management-call', { prospect: history.id }));
                                        }
                                    }"
                                >
                                    {{ history.contact_name }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ new Date(history.date_relance_modified).toLocaleDateString('fr-FR', { 
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) }}
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <div class="text-sm text-gray-600 mb-1 font-medium">Note:</div>
                                <div class="text-sm text-gray-600">{{ history.note ? history.note : 'Aucune note' }}</div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <div>
                                    <div class="text-sm text-gray-600 mb-1 font-medium">Ancien statut:</div>
                                    <div class="text-sm text-gray-600">{{ getActionLabel(history.old_status_relance) }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600 mb-1 font-medium">Nouveau statut:</div>
                                    <div class="text-sm text-gray-600">{{ getActionLabel(history.new_status_relance) }}</div>
                                </div>
                            </div>
                            
                            <div class="mt-2 text-right">
                                <span class="text-xs text-gray-500">Modifié par: {{ history.modified_by_relance }}</span>
                            </div>
                        </div>
                        
                        <div v-if="callHistory.length === 0" class="p-4 text-center text-sm text-gray-500">
                            Aucun historique disponible pour les 7 derniers jours.
                        </div>
                    </div>
                </div>

                <!-- Historique des 7 derniers jours - Vue desktop -->
                <div class="hidden lg:block mt-8 bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">
                        <i class="fas fa-history text-[#005692] mr-2"></i>
                        Historique des modifications des demandes de rappel
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 rounded-xl overflow-hidden">
                            <thead>
                                <tr class="bg-gradient-to-r from-[#005692] to-[#0078c9] text-white">
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">Nom</th>
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">Date de modification</th>
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">Note du statut</th>
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">Ancien statut</th>
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">Nouveau statut</th>
                                    <th class="px-6 py-4 text-left text-sm font-medium tracking-wider">Modifié par</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="(history, index) in sortedHistory" :key="index" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span 
                                            class="px-2 py-1 rounded-md cursor-pointer hover:opacity-80"
                                            :class="history.type === 'client' ? 'bg-blue-100 text-blue-800' : 'bg-[#f5e9c4] text-[#60501e]'"
                                            @click="() => {
                                                if (history.type === 'client') {
                                                    router.visit(route('management-call.client', { id: history.id }));
                                                } else {
                                                    router.visit(route('management-call', { prospect: history.id }));
                                                }
                                            }"
                                        >
                                            {{ history.contact_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ new Date(history.date_relance_modified).toLocaleDateString('fr-FR', { 
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ history.note ? history.note : 'Aucune note' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ getActionLabel(history.old_status_relance) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ getActionLabel(history.new_status_relance) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ history.modified_by_relance }}</td>
                                </tr>
                                <tr v-if="callHistory.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Aucun historique disponible pour les 7 derniers jours.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ajout d'une petite animation de survol sur les lignes de la table */
.transition-colors:hover {
    transition: background-color 0.2s ease;
}

/* Style pour les cartes sur mobile */
@media (max-width: 1023px) {
    .whitespace-pre-wrap {
        max-height: 100px;
        overflow-y: auto;
    }
}
</style>