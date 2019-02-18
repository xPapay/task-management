<template>
    <button class="fixed-button" @click="clickHandler">
        <slot></slot>
    </button>
</template>

<script>
export default {
    props: ['clickHandler']
}
</script>

<style>
.fixed-button {
    background: #40CCAB;
    border: none;
    outline: none;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    color: white;
    position: fixed;
    right: 10px;
    bottom: 20px;
}
</style>
