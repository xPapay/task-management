<template>
    <v-date-picker
        mode="range" 
        :value="value" 
        @input="$emit('input', $event);"
        :attributes="attrs"
        :theme-styles="themeStyles"
        tint-color="#40CCAB"
        is-expanded
        show-caps
    >
        <div class="input-group m-bottom-s" slot-scope="{inputValue, updateValue}">
            <i class="icon-calendar icon-2x"></i>
            <input
                name="start_date"
                class="input"
                type="text"
                :value="inputValue"
                placeholder="YYYY/MM/DD - YYYY/MM/DD"
                @change="updateValue($event.target.value)"
            >
        </div>
    </v-date-picker>
</template>
<script>
export default {
    props: ['value'],
    data() {
        return {
            attrs: [
                {
                    key: 'today',
                    contentStyle: {
                        color: 'black',
                        border: '1px solid #999999'
                    },
                    dates: new Date()
                }
            ],
            themeStyles: {
                wrapper: {
                    background: 'white',
                    border: '1px solid #EBEBEB',
                    borderRadius: '15px',
                    boxShadow: '2px 4px 8px 2px rgba(50, 50, 50, 0.18)',
                    minWidth: '280px',
                }
            }
        }
    }
}
</script>
