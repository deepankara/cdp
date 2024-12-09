    <div id="editor-container" style="height: 600px;"></div>
    <script src="https://editor.unlayer.com/embed.js"></script>
    <script>
        // Initialize Unlayer editor
        unlayer.init({
        id: 'editor-container',
        projectId: 251518, // Replace with your actual project ID
        displayMode: 'email',
        mergeTags: {
            // Define custom merge tags
            first_name: {
            name: "First Name",
            value: "{first_name}"
            },
            last_name: {
            name: "Last Name",
            value: "{last_name}"
            },
            email: {
            name: "Email Address",
            value: "{email}"
            },
            custom_field: {
            name: "Custom Field",
            value: "{custom_field}"
            }
        }
        });

        // Function to load a template by its ID
        function loadTemplate(templateId) {
        unlayer.loadTemplate(templateId, function () {
            console.log('Template loaded successfully:', templateId);
        }, function (error) {
            console.error('Failed to load template:', error);
        });
        }

        // Example of using merge tags in an email
        unlayer.addEventListener('design:loaded', function () {
        console.log('Design loaded successfully. You can now insert merge tags!');
        });
    </script>
