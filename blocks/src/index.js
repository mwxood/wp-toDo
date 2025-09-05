import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('wp-todo/block', {
    edit() {
        return <div {...useBlockProps()}>To-Do Block Editor</div>;
    },
    save() {
        return <div {...useBlockProps()}>To-Do Block Frontend</div>;
    },
});
