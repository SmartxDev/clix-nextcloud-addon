<template>
	<Content :class="{'icon-loading': loading}" app-name="vueexample">
		<AppNavigation>
			<template id="app-vueexample-navigation" #list>
				<div class="upload-cont" style="padding: 10px; border-bottom: 1px solid #f3f3f3;">
					<input type="file"
						accept="application/pdf"
						style="font-weight: normal; width: 100%; border: none;"
						@change="filesSelected" />
				</div>
			</template>
		</AppNavigation>
		<AppContent>
			<WebViewer :path="`${publicPath}lib`" url="http://66.175.217.67/nextclou/apps/vueexample/img/sample.pdf" />
			<Modal v-if="modal" @close="closeModal">
				<div class="modal__content" style="width: 50vw; text-align: center; margin: 10vw 0;">{{modalMessage}}</div>
			</Modal>
		</AppContent>
	</Content>
</template>

<script>
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import WebViewer from './components/WebViewer.vue'
import axios from '@nextcloud/axios'
import Modal from '@nextcloud/vue/dist/Components/Modal'

export default {
	name: 'App',
	components: {
		Content,
		AppContent,
		AppNavigation,
		WebViewer,
		Modal,
	},
	data() {
		return {
			publicPath: 'http://66.175.217.67/nextclou/apps/vueexample/public/',
			file: null,
			name: '',
			modal: false,
			modalMessage: '',
		}
	},
	mounted() {
		console.info('adadasdasd')
	},
	methods: {
		filesSelected(event) {
			console.info(event.target.files)
			this.file = event.target.files[0]
			const formData = new FormData()
			formData.append('file', event.target.files[0])
			axios.post('http://66.175.217.67/nextclou/index.php/apps/vueexample/upload', formData, {
				'Content-Type': 'multipart/form-data'
			}).then(res => {
				console.info('parsed', JSON.parse(res.data))
				this.modalMessage = ''
				if (JSON.parse(res.data).code === '200') {
					this.showModal()
					this.modalMessage = 'File uploaded successfully!'
				} else {
					this.showModal()
					this.modalMessage = 'Something went wrong!'
				}
			}).catch(err => {
				console.info('Something went wrong', err)
			})
		},
		showModal() {
			this.modal = true
		},
		closeModal() {
			this.modal = false
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
