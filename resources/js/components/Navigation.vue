<script>
export default {
    props: {
        attention: {
            default: () => false
        }
    },

    data() {
        return {
            isOpen: false
        }
    }
}
</script>

<style>
.hamburger {
    width: 30px;
    height: 23px;
    position: relative;
    float: right;
    top: 5px;
    right: 5px;
    display: flex;
    align-items: center;
    cursor: pointer;
}

.hamburger--attention:not(.hamburger--open)::after {
    content: '';
    display: block;
    position: absolute;
    bottom: 0;
    left: 0;
    transform: translate(-50%, 50%);
    width: 10px;
    height: 10px;
    background: #FF5858;
    border-radius: 50%;
}

.hamburger__line {
    width: 100%;
    height: 3px;
    background: #333;
}

.hamburger__line,
.hamburger__line::before,
.hamburger__line::after {
    transition: all 0.2s;
}

.hamburger__line::after,
.hamburger__line::before {
    content: '';
    display: block;
    width: 100%;
    height: 3px;
    border-radius: 2px;
    transform-origin: center;
    background: inherit;
}

.hamburger__line::before {
    position: absolute;
    top: 0;
}

.hamburger__line::after {
    position: absolute;
    bottom: 0;
}

.hamburger--open .hamburger__line {
    width: 0;
}

.hamburger--open .hamburger__line::before {
    transform: translateY(-50%) rotate(45deg);
    top: 50%;
}

.hamburger--open .hamburger__line::after {
    transform: translateY(50%) rotate(-45deg);
    bottom: 50%;
}

.navigation {
    display: inline-block;
    z-index: 999;
}

.navigation-menu {
    background: white;
    box-shadow: -2px 2px 10px 0 rgba(100, 100, 100, 0.5);
    padding: 30px 10px 10px 20px;
    display: none;
}

.navigation-menu--open {
    display: block;
}

.navigation-menu__item > a {
    display: block;
    margin: 5px 0;
    padding: 5px 0;
}

</style>
