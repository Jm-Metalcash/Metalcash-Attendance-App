<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Header from "@/Components/Header.vue";
import Legend from "@/Components/Legend.vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, computed } from 'vue';

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
};

const getContactAction = (contact) => {
    if (!(contact.id in selectedActions.value)) {
        selectedActions.value[contact.id] = '';
    }
    return selectedActions.value[contact.id];
};

const sortedContacts = computed(() => {
    return [...contactsToCall.value].sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        return sortDirection.value === 'desc' 
            ? dateB - dateA 
            : dateA - dateB;
    });
});

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
            type: currentContact.value.type
        });

        router.post('/update-action', {
            id: currentContact.value.id,
            type: currentContact.value.type,
            action: tempActionValue.value,
            old_status: oldStatus
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
            class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50 shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>Le statut a été mis à jour avec succès.</span>
            </div>
        </div>

        <!-- Boîte de dialogue de confirmation -->
        <div v-if="showConfirmDialog" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
                <h3 class="text-lg font-semibold mb-4">Confirmation</h3>
                <p class="mb-6">
                    Voulez-vous vraiment définir le statut de <span class="font-semibold">{{ currentContact?.name }}</span> 
                    sur <span class="font-semibold">{{ getActionLabel(tempActionValue) }}</span> ?
                </p>
                <div class="flex justify-end space-x-3">
                    <button 
                        @click="handleCancel"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                    <button 
                        @click="handleConfirm"
                        class="px-4 py-2 bg-[#005692] text-white rounded hover:bg-[#004572] transition-colors">
                        Confirmer
                    </button>
                </div>
            </div>
        </div>

        <div class="container flex-grow max-w-[1700px] mt-16 mx-auto px-4 sm:px-8 bg-white rounded-lg shadow-lg py-8 min-h-[800px]">
            <div class="p-6 rounded-lg text-center w-full bg-white shadow-md mb-8">
                <h2 class="text-gray-800 text-xl sm:text-2xl font-semibold">
                    <i class="fa-solid fa-clock-rotate-left text-[#005692] mr-2"></i> Liste de demandes de rappel
                </h2>
            </div>

            <!-- Tableau des contacts -->
            <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
                <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                    <thead>
                        <tr class="text-left">
                            <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs w-[15%]">
                                Nom
                            </th>
                            <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs w-[13%]">
                                Téléphone
                            </th>
                            <th 
                                @click="toggleSort"
                                class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs w-[9%] cursor-pointer hover:bg-gray-100"
                            >
                                Date
                                <i 
                                    class="fa-solid ml-1"
                                    :class="{
                                        'fa-sort-up text-xs': sortDirection === 'asc',
                                        'fa-sort-down text-xs': sortDirection === 'desc'
                                    }"
                                ></i>
                            </th>
                            <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs w-[35%]">
                                Note
                            </th>
                            <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs w-[10%]">
                                Statut
                            </th>
                            <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs w-[15%]">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="contact in sortedContacts" :key="contact.id" class="hover:bg-gray-50 text-sm">
                            <td class="border-b border-gray-200 px-6 py-4">
                                <span class="px-2 py-1 rounded-md"
                                    :class="contact.status === 'Client' ? 'bg-blue-100 text-blue-800' : 'bg-[#f5e9c4] text-[#60501e]'">
                                    {{ contact.name }}
                                </span>
                            </td>
                            <td class="border-b border-gray-200 px-6 py-4 text-gray-600">
                                {{ contact.phone || 'Aucun numéro enregistré' }}
                            </td>
                            <td class="border-b border-gray-200 px-6 py-4 text-gray-600">
                                {{ new Date(contact.date).toLocaleDateString('fr-FR') }}
                            </td>
                            <td class="border-b border-gray-200 px-6 py-4 text-gray-600">
                                {{ contact.note }}
                            </td>
                            <td class="border-b border-gray-200 px-6 py-4 text-gray-600">
                                {{ getActionLabel(contact.action_relance) }}
                            </td>
                            <td class="border-b border-gray-200 px-6 py-4">
                                <select
                                    :value="getContactAction(contact)"
                                    @change="confirmAction(contact, $event.target.value)"
                                    class="block w-full px-2 py-1 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-[#005692] focus:border-[#005692]">
                                    <option value="" disabled>Choisir une action</option>
                                    <option value="0">À contacter</option>
                                    <option value="1">Clôturé</option>
                                    <option value="2">Injoignable</option>
                                    <option value="3">Annuler</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Légende -->
            <Legend />

            <!-- Historique des 7 derniers jours -->
            <div class="mt-16 bg-white rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 px-4 py-4">
                    <i class="fas fa-history text-[#005692] mr-2"></i>
                    Historique des demandes de rappel traitées au cours des 7 derniers jours
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th 
                                    @click="historySort = historySort === 'desc' ? 'asc' : 'desc'"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                >
                                    Date de modification
                                    <i 
                                        class="fa-solid ml-1"
                                        :class="{
                                            'fa-sort-up text-xs': historySort === 'asc',
                                            'fa-sort-down text-xs': historySort === 'desc'
                                        }"
                                    ></i>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ancien statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nouveau statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modifié par</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(history, index) in sortedHistory" :key="index" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 py-1 rounded-md"
                                        :class="history.type === 'client' ? 'bg-blue-100 text-blue-800' : 'bg-[#f5e9c4] text-[#60501e]'">
                                        {{ history.contact_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(history.date_relance_modified).toLocaleDateString('fr-FR', { 
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ getActionLabel(history.old_status_relance) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ getActionLabel(history.new_status_relance) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ history.modified_by_relance }}</td>
                            </tr>
                            <tr v-if="callHistory.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Aucun historique disponible pour les 7 derniers jours
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Légende -->
            <Legend />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.table-striped tr:nth-child(even) {
    background-color: rgba(0, 0, 0, 0.02);
}
</style>
