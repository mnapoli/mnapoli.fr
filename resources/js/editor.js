import { createApp, markRaw } from 'vue/dist/vue.esm-bundler.js';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'easymde/dist/easymde.min.css';

const { default: EasyMDE } = await import('easymde');

const initial = JSON.parse(document.getElementById('editor-data').textContent);

createApp({
    data() {
        return {
            title: initial.title,
            content: initial.content,
            image: initial.image,
            mde: null,
            imagePreviewUrl: null,
        };
    },
    computed: {
        warningMore() {
            return !this.content.includes('<!--more-->');
        },
    },
    mounted() {
        this.mde = markRaw(new EasyMDE({
            element: this.$refs.editor,
            initialValue: this.content,
            forceSync: true,
            autofocus: true,
            autoDownloadFontAwesome: false,
            spellChecker: false,
            inputStyle: 'contenteditable',
            nativeSpellcheck: true,
            previewClass: ['editor-preview', 'prose', 'max-w-none'],
            toolbar: [
                'bold', 'italic', 'heading', '|',
                {
                    name: 'more',
                    action: (editor) => editor.codemirror.replaceSelection('<!--more-->'),
                    className: 'fa fa-ellipsis-h',
                    title: 'Mark the end of the extract of the blog post.',
                },
                'code', 'quote', '|', 'unordered-list', 'ordered-list', '|',
                'link', 'image', 'table', '|', 'preview', 'side-by-side', 'fullscreen', '|', 'guide',
            ],
            previewRender(markdown, preview) {
                fetch(initial.previewUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': initial.csrfToken,
                    },
                    body: JSON.stringify({ markdown }),
                })
                    .then((response) => {
                        if (!response.ok) throw new Error('Preview failed');
                        return response.json();
                    })
                    .then((response) => { preview.innerHTML = response.html; })
                    .catch(() => { preview.textContent = 'The preview could not be loaded.'; });

                return 'Loading…';
            },
            uploadImage: true,
            imageUploadFunction(file, onSuccess, onError) {
                const data = new FormData();
                data.append('image', file);
                data.append('directory', initial.directory);

                fetch(initial.uploadUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': initial.csrfToken,
                    },
                    body: data,
                })
                    .then((response) => {
                        if (!response.ok) throw new Error('Upload failed');
                        return response.json();
                    })
                    .then((response) => onSuccess(response.path))
                    .catch(() => onError('The image could not be uploaded.'));
            },
        }));
        this.mde.codemirror.on('change', () => {
            this.content = this.mde.value();
        });
    },
    beforeUnmount() {
        this.mde?.toTextArea();
        if (this.imagePreviewUrl) URL.revokeObjectURL(this.imagePreviewUrl);
    },
    methods: {
        uploadedImageChange(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (this.imagePreviewUrl) URL.revokeObjectURL(this.imagePreviewUrl);
            this.imagePreviewUrl = URL.createObjectURL(file);
            this.image = this.imagePreviewUrl;
        },
    },
}).mount('#app');
