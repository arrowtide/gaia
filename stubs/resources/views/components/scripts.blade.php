<script type="module">

    Alpine.data('drawerComponent', (name, el, duration, parents) => ({
        open: false,
        el: el,

        openDrawer() {
            this.open = true;

            el.showModal();

            this.$nextTick(() => {
                this.$refs.drawer.focus(); 
            });
        },

        closeAllDrawers() {
            function getAllDrawerParents(element) {
                let parents = [];
                while (element) {
                    element = element.parentElement;
                    if (element && element.hasAttribute('data-drawer-component')) {
                        parents.push(element);
                    }
                }
                return parents;
            }

            const parents = getAllDrawerParents(this.$root);
            console.log(parents);

            parents.forEach(parent => {
                parent.querySelector('[data-drawer-inner]').style.transitionDuration = `0ms`;
                parent.querySelector('[data-drawer-inner]').style.opacity = '0';
            });

            this.closeDrawer();

            parents.forEach(parent => {
                this.$dispatch(`drawer:close:${parent.dataset.drawerComponent}`);
            });

            setTimeout(() => {
                parents.forEach(parent => {
                    parent.style.transitionDuration = ``;
                    parent.style.opacity = '';
                });
            }, duration);
        },

        closeDrawer() {

            this.$nextTick(() => {
                this.open = false;

                setTimeout(() => {
                    el.close();

                    this.$refs.drawer.style.transitionDuration = `${ duration }ms`;
                    this.$refs.drawer.style.opacity = '';
                }, duration);
            });
        }
    }));

    Alpine.data('componentDropdown', (uid, anchor) => ({
        open: false,
        init() {
            const focusables = this.$focus.within(this.$refs.dropdown).focusables();
            focusables.forEach(el => {
                el.tabIndex = '-1';
            });
        },
        toggle(open) {
            this.open = open || ! this.open;
        },
        close() {
            this.open = false;
        },
        dropdownClose() {
            this.open = false;
        },
        container: {
            ['@keydown.tab']($event) {
                this.close();
            },
        },
        button: {
            [':aria-haspopup']() {
                return this.open;
            },
            [':aria-expanded']() {
                return this.open;
            },
            ['@click']() {
                this.toggle();
            },
            ['@keydown.down.prevent']() {
                if (this.open) {
                    let firstFocusableEl = this.$focus.within(this.$refs.dropdown).getFirst();
                    this.$focus.focus(firstFocusableEl);

                    return;
                }

                this.toggle();
            },
            [':data-active']() {
                return this.open;
            },
            'type': 'button',
            'aria-haspopup': 'menu',
            'id': `dropdown-button-${ uid }`,
            'aria-controls': `dropdown-panel-${ uid }`,
        },
        dropdown: {
            ['x-show']() {
                return this.open;
            },
            ['@click.away']() {
                this.open = false;

                this.$nextTick(() =>{
                    this.$refs.button.focus();
                });
            },
            ['@keydown.down.prevent']() {
                this.$focus.wrap().next();
            },
            ['@keydown.up.prevent']() {
                this.$focus.wrap().previous();
            },
            [`x-anchor.${anchor}`]() {
                return this.$refs.button;
            },
            'id': `dropdown-panel-${ uid }`,
            'role': 'menu',
            'tabindex': '-1',
            'aria-labelledby': `dropdown-button-${ uid }`,
            'aria-orientation': 'vertical',
            'x-transition:enter': 'transition ease-out duration-200',
            'x-transition:enter-start': 'opacity-0 translate-y-2',
            'x-transition:enter-end': 'opacity-100 translate-y-0',
            'x-transition:leave': 'transition ease-in duration-150',
            'x-transition:leave-start': 'opacity-100',
            'x-transition:leave-end': 'opacity-0',
        }
    }));

    Alpine.data('modalComponent', (name, el, duration) => ({
        open: false,
        el: el,

        $open() {
            this.open = true;

            el.showModal();
        },

        $closeAll() {
            function getAllmodalParents(element) {
                let parents = [];
                while (element) {
                    element = element.parentElement;
                    if (element && element.hasAttribute('data-modal-component')) {
                        parents.push(element);
                    }
                }
                return parents;
            }

            const parents = getAllmodalParents(this.$root);
            console.log(parents);

            parents.forEach(parent => {
                parent.querySelector('[data-modal-inner]').style.transitionDuration = `0ms`;
                parent.querySelector('[data-modal-inner]').style.opacity = '0';
            });

            this.$close();

            parents.forEach(parent => {
                this.$dispatch(`modal:close:${parent.dataset.modalComponent}`);
            });

            setTimeout(() => {
                parents.forEach(parent => {
                    parent.style.transitionDuration = ``;
                    parent.style.opacity = '';
                });
            }, duration);
        },

        $close() {
            this.$nextTick(() => {
                this.open = false;

                setTimeout(() => {
                    el.close();

                    this.$refs.modal.style.transitionDuration = `${ duration }ms`;
                    this.$refs.modal.style.opacity = '';
                }, duration);
            });
        }
    }));
</script>
