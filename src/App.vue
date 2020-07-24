<template>
	<Content :class="{'icon-loading': loading}" app-name="vueexample">
		<AppNavigation>
			<template id="app-vueexample-navigation" #list>
				<h1>File</h1>
			</template>
		</AppNavigation>
		<AppContent>
			<WebViewer :path="`${publicPath}lib`" url="http://66.175.217.67/nextclou/apps/vueexample/img/sample.pdf" />
		</AppContent>
	</Content>
</template>

<script>
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import WebViewer from './components/WebViewer.vue'
import axios from '@nextcloud/axios'
import { loadState } from '@nextcloud/initial-state'

export default {
	name: 'App',
	components: {
		Content,
		AppContent,
		AppNavigation,
		WebViewer,
	},
	data() {
		return {
			publicPath: 'http://66.175.217.67/nextclou/apps/vueexample/public/',
			file: null,
			name: '',
		}
	},
	mounted() {
		console.info('asdadadadasd')
		const val = loadState('vueexample', 'files')
		console.info('adadasdasd', val)
		axios.get('http://66.175.217.67/nextclou/ocs/v2.php/core/whatsnew?format=json')
	},
	methods: {
		filesSelected(event) {
			this.file(event.target.files)
			this.name = event.target.files[0].name
		},
		addOption(val) {
			this.options.push(val)
			this.select.push(val)
		},
		previous(data) {
			console.debug(data)
		},
		next(data) {
			console.debug(data)
		},
		close(data) {
			console.debug(data)
		},
		newButtonAction(e) {
			console.debug(e)
		},
		log(e) {
			console.debug(e)
		},
	},
}
</script>

<style>
	.app-navigation-toggle {
		display: none;
	}
</style>
