<script>
    import Replies from '../components/Replies.vue';

    export default {
        props: ['initialRepliesCount'],

        components: { Replies },

        data() {
            return {
                repliesCount: 10
            }
        }
    }
</script>