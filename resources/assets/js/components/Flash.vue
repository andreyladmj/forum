<template>
    <div class="alert alert-flash" :class="alertClass" role="alert" v-show="show">
        <strong>Success!</strong> {{ body }}
    </div>
</template>

<script>
    export default {
        props: ['message', 'type'],

        data() {
            return {
                body: this.message,
                alertClass: '',
                show: false
            }
        },

        created() {
            if(this.message) {
                this.flash(this.message)
            }

            window.events.$on('flash', (message, type) => this.flash(message, type));
        },

        methods: {
            flash(message, type) {
                this.body = message;
                this.alertClass = type || 'alert-success';
                this.show = true;

                this.hide();
            },
            hide() {
                setTimeout(() => {
                    this.show = false;
                }, 3000)
            }
        }
    };
</script>

<style>
    .alert-flash {
        position: fixed;
        bottom: 25px;
        right: 25px;
    }
</style>