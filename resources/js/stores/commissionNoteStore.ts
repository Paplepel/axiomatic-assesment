import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { CommissionNote } from '@/types'

/**
 * Manages UI state for the Commission Notes page:
 *   - which note is being edited (selectedNote)
 *   - whether the "add note" slide-over panel is open (addPanelOpen)
 */
export const useCommissionNoteStore = defineStore('commissionNote', () => {
    const selectedNote = ref<CommissionNote | null>(null)
    const addPanelOpen = ref(false)

    function selectNote(note: CommissionNote) {
        selectedNote.value = note
        addPanelOpen.value = false
    }

    function clearSelection() {
        selectedNote.value = null
    }

    function openAddPanel() {
        selectedNote.value = null
        addPanelOpen.value = true
    }

    function closeAddPanel() {
        addPanelOpen.value = false
    }

    return {
        selectedNote,
        addPanelOpen,
        selectNote,
        clearSelection,
        openAddPanel,
        closeAddPanel,
    }
})
