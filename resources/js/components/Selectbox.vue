<template>
<div :class="$attrs.class">
    <ul class="inline-list m-bottom-s">
        <li 
            v-for="(item, index) in selected" 
            :key="index"
            v-on="editable ? { click: () => deselect(item) } : {}"
            class="valign"
        >
            <img 
                :src="item.picture_path" 
                :alt="item.name" 
                class="photo-big photo-circle"
            >
            <span v-text="item.name" class="m-left-s"></span>
        </li>
    </ul>
    <div class="selectbox" :class="{'selectbox--no-results': !hasResults}" v-if="editable">
        <div class="selectbox__input-group valign">
            <i class="fas fa-user fa-2x f-no-shrink"></i>
            <input 
                :name="$attrs.name"
                type="text" 
                v-model="query" 
                @input="handleQueryChange"
                class="selectbox__input m-left-xs"
            >
            <div 
                class="selectbox__collapse m-left-xs" 
                v-show="hasResults"
                @click="clearQuery"
            >
                <i class="fas fa-arrow-circle-up fa-2x gray"></i>
            </div>
        </div>
        <ul class="selectbox__options" v-if="hasResults">
            <li 
                v-for="(item, index) in results" 
                :key="index"
                @click="toggleSelection(item)"
                class="selectbox__option valign"
                :class="{'selectbox__option--selected': isSelected(item)}"
            >
                <img 
                    :src="item.picture_path" 
                    :alt="item.name" 
                    class="photo-small photo-circle"
                >
                <span class="m-left-xs" v-html="highlightMatch(item.name)"></span>
            </li>
        </ul>
    </div>
</div>
</template>

<script>
import { debounce } from 'lodash';
import axios from 'axios';
export default {
    inheritAttrs: false,
    props: {
        selected: {
            type: Object,
            default: () => ({})
        },
        editable: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            query: '',
            results: []
        }
    },

    computed: {
        hasResults() {
            return this.results.length > 0;
        }
    },

    methods: {
        handleQueryChange: debounce(function() {
            if (this.query.length < 3) {
                this.clearQueryResults();
                return;
            }
            this.queryResults();
        }, 500),
        
        async queryResults() {
            let { data } = await axios.get('/users', { 
                params: {
                    nameLike: this.query
                }
            });
            this.results = [...data];
        },

        toggleSelection(item) {
            return this.isSelected(item) ? this.deselect(item) : this.select(item);
        },

        deselect(item) {
            this.$emit('deselect', item);
        },

        select(item) {
            this.$emit('select', item);
        },

        isSelected(item) {
            return Object.keys(this.selected).findIndex(id => id == item.id) != -1;
        },

        highlightMatch(text) {
            const regex = new RegExp(this.query, 'i');
            return text.replace(regex, "<span class='selectbox__match'>$&</span>");
        },

        clearQueryResults() {
            this.results = [];
        },

        clearQuery() {
            this.query = '';
            this.results = [];
        }
    }
}
</script>
