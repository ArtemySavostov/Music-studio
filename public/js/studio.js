document.addEventListener('livewire:init', () => {
    const notify = (failed) => window.dispatchEvent(new CustomEvent('studio-connection', { detail: { failed } }));

    Livewire.interceptRequest(({ onFailure, onSuccess }) => {
        onFailure(() => notify(true));
        onSuccess(() => notify(false));
    });
});
