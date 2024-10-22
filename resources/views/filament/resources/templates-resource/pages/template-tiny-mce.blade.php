<x-filament-panels::page>
    <x-filament-panels::form>
        {{ $this->form }}
    <textarea id="html_content" name="html_content" rows="4" cols="50">
        At w3schools.com you will learn how to make a website. They offer free tutorials in all web development technologies.
    </textarea>
    </x-filament-panels::form>
    <script>
        document.addEventListener('DOMContentLoaded', function (){
            tinymce.init({
                selector: 'textarea',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            });
        });
    </script>
</x-filament-panels::page>