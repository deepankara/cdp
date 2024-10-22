<!-- 

    <script>
        document.addEventListener('DOMContentLoaded', function (){
            tinymce.init({
                selector: 'textarea',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            });
            sessionStorage.setItem("textAreaLoaded",true);
        });

        if(sessionStorage.getItem("textAreaLoaded") == 'true'){
            tinymce.init({
                selector: 'textarea',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            });
        }
    </script>
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <textarea id="{{ $getId() }}" name="{{ $getName() }}">{{ $getState() }}</textarea>
    </x-dynamic-component> -->
