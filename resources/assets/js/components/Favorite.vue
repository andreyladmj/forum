<template>
    <button type="submit" :class="classes" @click="toggle">
        <span class="glyphicon" :class="favoritedClass"></span>
        <span v-text="favoritesCount"></span>
    </button>
</template>

<script>
    export default {
        props: ['reply'],
        data() {
            return {
                favoritesCount: this.reply.favoritesCount,
                isFavorited: this.reply.isFavorited
            }
        },

        computed: {
            classes() {
                return ['btn', this.isFavorited ? 'btn-primary' : 'btn-default']
            },
            favoritedClass() {
                return this.isFavorited ? 'glyphicon-heart' : 'glyphicon-heart-empty'
            }
        },

        methods: {
            toggle() {
                if(this.isFavorited) {
                    this.remove();
                } else {
                    this.create();
                }

                this.isFavorited = 1 - this.isFavorited;
            },

            create() {
                axios.post('/replies/' + this.reply.id + '/favorites');
                this.favoritesCount++;
            },
            remove() {
                axios.delete('/replies/' + this.reply.id + '/favorites');
                this.favoritesCount--;
            }
        }
    }
</script>