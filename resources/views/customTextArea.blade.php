@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('#name');
    input.addEventListener('input', function(event) {
        const value = event.target.value;
        const newValue = value.replace(/[^a-z0-9_]/g, '');
        event.target.value = newValue;
    });

    const textarea = document.querySelector('textarea[id="html_content"]');
    const actions = document.querySelectorAll('[data-action]');

    actions.forEach(action => {
        action.addEventListener('click', () => {
            const selectionStart = textarea.selectionStart;
            const selectionEnd = textarea.selectionEnd;
            const selectedText = textarea.value.substring(selectionStart, selectionEnd);

            if (selectedText) {
                let formattedText;

                // Determine the formatting action
                switch (action.dataset.action) {
                    case 'italic':
                        formattedText = `*${selectedText}*`;
                        break;
                    case 'bold':
                        formattedText = `**${selectedText}**`;
                        break;
                    case 'strike':
                        formattedText = `~~${selectedText}~~`;
                        break;
                    case 'monospace':
                        formattedText = `\`${selectedText}\``;
                        break;
                    default:
                        formattedText = selectedText;
                }

                // Replace the selected text with formatted text
                textarea.value = 
                    textarea.value.substring(0, selectionStart) + 
                    formattedText + 
                    textarea.value.substring(selectionEnd);

                // Maintain cursor position after formatting
                textarea.setSelectionRange(
                    selectionStart, 
                    selectionStart + formattedText.length
                );

                // Trigger a change event to notify if needed
                textarea.dispatchEvent(new Event('input'));
            }
        });
    });
});

</script>
@endpush
