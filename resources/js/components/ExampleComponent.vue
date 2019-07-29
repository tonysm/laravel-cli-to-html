<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <loading
                        :active="loading"
                        :is-full-page="false"
                        :can-cancel="false"
                    />
                    <div class="card-header d-flex justify-content-between align-items-center">
                        Example Component

                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newCommandModal">
                            New Command
                        </button>
                    </div>

                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Command</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-active" v-for="cmd in commands" :key="cmd.id">
                                    <th scope="row">{{ cmd.command }}</th>
                                    <td>
                                        {{ cmd.completed_at ? 'Completed' : 'Running...' }}
                                        <span v-if="cmd.completed_at" class="badge text-uppercase" :class="{'badge-success': cmd.exit_code === 0, 'badge-danger': cmd.exit_code !== 0}">{{ cmd.exit_code === 0 ? 'Successfully' : 'With error' }}</span>
                                    </td>
                                    <td>
                                        <div class="modal fade" :id="`output${cmd.id}`" tabindex="-1" role="dialog" :aria-labelledby="`exampleModalLabel${cmd.id}`" aria-hidden="true">
                                            <div class="modal-dialog modal-xl" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" :id="`exampleModalLabel${cmd.id}`">Command Output</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <pre style="background-color: black; overflow: auto; padding: 10px 15px; font-family: monospace;" v-html="cmd.output_html"></pre>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light" data-toggle="modal" :data-target="`#output${cmd.id}`" :disabled="!cmd.completed_at" @click.prevent="showOutput(cmd)">View output</button>
                                    </td>
                                </tr>

                                <tr v-if="commands.length === 0 && !loading" colspan="3">
                                    No commands yet.
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="newCommandModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form @submit.prevent="saveCommand">
                        <loading
                            :active="saving"
                            :is-full-page="false"
                            :can-cancel="false"
                        />
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">New Command</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="commandInput">Command</label>
                                <input type="text" class="form-control" id="commandInput" placeholder="" v-model="newCommand">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">
                                Execute
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';

    export default {
        components: {
            Loading,
        },
        data() {
            return {
                loading: true,
                commands: [],
                saving: false,
                newCommand: '',
            };
        },
        methods: {
            fetchCommands(updateLoading = true) {
                if (updateLoading) this.loading = true;

                axios.get('/commands')
                    .then(({data}) => {
                        this.commands = data.data;
                        if (updateLoading) this.loading = false;
                    });
            },
            showOutput(cmd) {
                this.$modal.show(`output${cmd.id}`, {
                    title: 'Command output',
                    text: cmd.output,
                    buttons: [
                        {
                            title: 'Close',
                        },
                    ],

                });
            },
            saveCommand() {
                this.saving = true;

                axios.post('/commands', {
                    command: this.newCommand,
                }).then(({data}) => {
                    this.newCommand = '';
                    this.saving = false;
                    $('#newCommandModal').modal('hide');
                    this.fetchCommands(false);
                });
            },
        },
        mounted() {
            this.fetchCommands();

            setInterval(() => {
                this.fetchCommands(false);
            }, 5000);
        }
    }
</script>
