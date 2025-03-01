<x-moonshine::layout.grid xmlns:x-moonshine="http://www.w3.org/1999/html" x-data="data">
    <x-moonshine::layout.column colSpan="10">
        <x-moonshine::layout.box>
            <div class="flex justify-between">
                <div class="flex gap-2">
                    <x-moonshine::link-button href="#"
                                              x-on:click="fetchLogs()"
                    >
                        <x-moonshine::icon icon="arrow-path"/>
                        <span>
                            {{ __('moonshine-log-viewer::log-viewer.refresh') }}
                        </span>
                    </x-moonshine::link-button>
                    <x-moonshine::link-button href="#"
                                              x-on:click="togglePlay()"
                    >
                        <span x-show="!refreshIntervalId"><x-moonshine::icon icon="play"/></span>
                        <span x-show="refreshIntervalId"><x-moonshine::icon icon="pause"/></span>
                    </x-moonshine::link-button>
                    <x-moonshine::link-button href="#"
                                              x-on:click="prevPage()"
                                              x-show="prevUrl"
                    >
                        <x-moonshine::icon icon="chevron-left"/>
                    </x-moonshine::link-button>
                    <x-moonshine::link-button href="#"
                                              x-on:click="nextPage()"
                                              x-show="nextUrl"
                    >
                        <x-moonshine::icon icon="chevron-right"/>
                    </x-moonshine::link-button>
                </div>
                <div>
                    <x-moonshine::off-canvas
                            title="{{ __('moonshine-log-viewer::log-viewer.filters') }}"
                            :left="false"
                    >
                        <x-slot:toggler>
                            {{ __('moonshine-log-viewer::log-viewer.filters') }}
                        </x-slot:toggler>

                        <x-moonshine::form.label name="{{ __('moonshine-log-viewer::log-viewer.level') }}">
                            {{ __('moonshine-log-viewer::log-viewer.level') }}
                        </x-moonshine::form.label>
                        <div class="flex flex-col mb-4">
                            <x-moonshine::form.label>
                                <x-moonshine::form.input x-model="filter_level" type="checkbox" value="Emergency"/>
                                Emergency
                            </x-moonshine::form.label>
                            <x-moonshine::form.label>
                                <x-moonshine::form.input x-model="filter_level" type="checkbox" value="Alert"/>
                                Alert
                            </x-moonshine::form.label>
                            <x-moonshine::form.label>
                                <x-moonshine::form.input x-model="filter_level" type="checkbox" value="Critical"/>
                                Critical
                            </x-moonshine::form.label>
                            <x-moonshine::form.label>
                                <x-moonshine::form.input x-model="filter_level" type="checkbox" value="Error"/>
                                Error
                            </x-moonshine::form.label>
                            <x-moonshine::form.label>
                                <x-moonshine::form.input x-model="filter_level" type="checkbox" value="Warning"/>
                                Warning
                            </x-moonshine::form.label>
                            <x-moonshine::form.label>
                                <x-moonshine::form.input x-model="filter_level" type="checkbox" value="Notice"/>
                                Notice
                            </x-moonshine::form.label>
                            <x-moonshine::form.label>
                                <x-moonshine::form.input x-model="filter_level" type="checkbox" value="Info"/>
                                Info
                            </x-moonshine::form.label>
                            <x-moonshine::form.label>
                                <x-moonshine::form.input x-model="filter_level" type="checkbox" value="Debug"/>
                                Debug
                            </x-moonshine::form.label>
                        </div>

                        <x-moonshine::form.label name="{{ __('moonshine-log-viewer::log-viewer.env') }}">
                            {{ __('moonshine-log-viewer::log-viewer.env') }}
                        </x-moonshine::form.label>
                        <x-moonshine::form.input
                                name="{{ __('moonshine-log-viewer::log-viewer.env') }}"
                                placeholder="{{ __('moonshine-log-viewer::log-viewer.env') }}"
                                x-model="filter_env"
                        />

                        <x-moonshine::form.label name="{{ __('moonshine-log-viewer::log-viewer.time_from') }}">
                            {{ __('moonshine-log-viewer::log-viewer.time_from') }}
                        </x-moonshine::form.label>
                        <x-moonshine::form.input
                                type="datetime-local"
                                x-model="filter_time_start"
                        />

                        <x-moonshine::form.label name="{{ __('moonshine-log-viewer::log-viewer.time_to') }}">
                            {{ __('moonshine-log-viewer::log-viewer.time_to') }}
                        </x-moonshine::form.label>
                        <x-moonshine::form.input
                                type="datetime-local"
                                x-model="filter_time_end"
                        />

                        <x-moonshine::form.label name="{{ __('moonshine-log-viewer::log-viewer.message') }}">
                            {{ __('moonshine-log-viewer::log-viewer.message') }}
                        </x-moonshine::form.label>
                        <x-moonshine::form.input
                                name="{{ __('moonshine-log-viewer::log-viewer.message') }}"
                                placeholder="{{ __('moonshine-log-viewer::log-viewer.message') }}"
                                x-model="filter_info"
                        />

                        <x-moonshine::form.button
                                x-on:click="filterReset()">{{ __('moonshine-log-viewer::log-viewer.reset') }}</x-moonshine::form.button>
                        <x-moonshine::form.button x-on:click="filterApply()"
                                                  class="btn-primary">{{ __('moonshine-log-viewer::log-viewer.apply') }}</x-moonshine::form.button>
                    </x-moonshine::off-canvas>
                </div>
            </div>
            <x-moonshine::table>
                <x-slot:thead>
                    <th>{{ __('moonshine-log-viewer::log-viewer.level') }}</th>
                    <th>{{ __('moonshine-log-viewer::log-viewer.env') }}</th>
                    <th>{{ __('moonshine-log-viewer::log-viewer.time') }}</th>
                    <th>{{ __('moonshine-log-viewer::log-viewer.message') }}</th>
                    <th></th>
                </x-slot:thead>
                <x-slot:tbody>
                    <template x-for="(log, index) in logs" :key="index">
                        <tr>
                            <td>
                                <span class="badge" x-bind:class="levelColor(log.level)" x-text="log.level"></span>
                            </td>
                            <td><strong x-html="log.env"></strong></td>
                            <td style="width:150px;" x-text="log.time"></td>
                            <td><code style="word-break: break-all;" x-text="log.info"></code></td>
                            <td>

                            </td>
                        </tr>
                    </template>
                </x-slot:tbody>
            </x-moonshine::table>
        </x-moonshine::layout.box>
    </x-moonshine::layout.column>
    <x-moonshine::layout.column colSpan="2">
        <x-moonshine::layout.box title="{{ __('moonshine-log-viewer::log-viewer.files') }}">
            <ul class="flex flex-col gap-2">
                <template x-for="logFile in logFiles">
                    <li>
                        <span class="flex gap-2" x-bind:class="logFile === fileName ? 'font-bold' : ''">
                            <template x-if="logFile === fileName">
                                <x-moonshine::icon icon="envelope-open"/>
                            </template>
                            <template x-if="logFile !== fileName">
                                <x-moonshine::icon icon="envelope"/>
                            </template>
                            <x-moonshine::link-native href="#"
                                                      x-on:click="selectFile(logFile)"
                                                      icon="envelope-open"
                                                      x-text="logFile"
                            />
                        </span>
                    </li>
                </template>
            </ul>
        </x-moonshine::layout.box>
        <x-moonshine::layout.divider/>
        <x-moonshine::layout.box title="{{ __('moonshine-log-viewer::log-viewer.info') }}">
            <ul>
                <li>
                    {{ __('moonshine-log-viewer::log-viewer.size') }}: <span x-text="size"></span>
                </li>
                <li>
                    {{ __('moonshine-log-viewer::log-viewer.updated_at') }}: <span x-text="lastUpdate"></span>
                </li>
            </ul>
        </x-moonshine::layout.box>
    </x-moonshine::layout.column>
</x-moonshine::layout.grid>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('data', () => ({
            isLoading: false,
            end: 0,
            fileName: null,
            logFiles: [],
            logs: [],
            size: "Unknown",
            refreshIntervalId: null,
            file: '',
            nextUrl: null,
            prevUrl: null,
            lastUpdate: null,
            filter_level: [],
            filter_env: '',
            filter_time_start: '',
            filter_time_end: '',
            filter_info: '',

            init() {
                this.fetchLogs();
            },

            fetch(url, params = {}, rewrite = true) {
                let _url = new URL(url);

                _url.searchParams.append('filter_level', this.filter_level);
                _url.searchParams.append('filter_env', this.filter_env);
                _url.searchParams.append('filter_time_start', this.filter_time_start);
                _url.searchParams.append('filter_time_end', this.filter_time_end);
                _url.searchParams.append('filter_info', this.filter_info);

                for (let key in params) {
                    if (params.hasOwnProperty(key)) {
                        _url.searchParams.append(key, params[key]);
                    }
                }

                fetch(_url.toString())
                    .then(res => res.json())
                    .then(data => {
                        this.isLoading = false;
                        this.end = data.end;
                        this.file = data.fileName;
                        this.fileName = data.fileName;
                        this.logFiles = data.logFiles;
                        this.logs = rewrite ? data.logs : [...data.logs, ...this.logs];
                        this.size = data.size;
                        this.nextUrl = data.nextUrl;
                        this.prevUrl = data.prevUrl;
                        this.lastUpdate = data.lastUpdate;
                    });
            },

            fetchLogs() {
                this.isLoading = true;
                this.fetch(`{{ route('moonshine.log.viewer.file') }}/${this.file}`);
            },

            fetchLastLogs() {
                this.fetch(`{{ route('moonshine.log.viewer.file') }}/${this.file}`, {offset: this.end}, false);
            },

            selectFile(file) {
                this.file = file;
                this.end = 0;
                this.stopPlay();
                this.filterReset();
            },

            togglePlay() {
                if (this.refreshIntervalId) {
                    clearInterval(this.refreshIntervalId);
                    this.refreshIntervalId = null;
                } else {
                    this.fetchLogs();
                    this.refreshIntervalId = setInterval(() => this.fetchLastLogs(), 2000);
                }
            },

            stopPlay() {
                if (this.refreshIntervalId) {
                    clearInterval(this.refreshIntervalId);
                    this.refreshIntervalId = null;
                }
            },

            prevPage() {
                this.stopPlay();
                this.fetch(this.prevUrl);
            },

            nextPage() {
                this.stopPlay();
                this.fetch(this.nextUrl);
            },

            levelColor(level) {
                const levelColors = {
                    EMERGENCY: 'badge-gray',
                    ALERT: 'badge-primary',
                    CRITICAL: 'badge-red',
                    ERROR: 'badge-error',
                    WARNING: 'badge-warning',
                    NOTICE: 'badge-blue',
                    INFO: 'badge-info',
                    DEBUG: 'badge-green',
                };
                return levelColors[level];
            },

            filterApply() {
                this.fetch(`{{ route('moonshine.log.viewer.file') }}/${this.file}`);
            },

            filterReset() {
                this.filter_level = [];
                this.filter_env = '';
                this.filter_time_start = '';
                this.filter_time_end = '';
                this.filter_info = '';
                this.fetchLogs();
            }
        }));
    });
</script>