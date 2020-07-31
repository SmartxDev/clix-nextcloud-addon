<template>
	<Content :class="{'icon-loading': loading}" app-name="vueexample">
		<AppNavigation>
			<template id="app-vueexample-navigation" #list>
				<div class="upload-cont" style="padding: 10px; border-bottom: 1px solid #f3f3f3;">
					<label class="upload">
						upload
						<input type="file"
							accept="application/pdf"
							style="font-weight: normal; width: 100%; border: none;"
							@change="filesSelected" />
					</label>
					<span v-if="file">{{file.name}}</span>
				</div>
				<div style="padding-left: 10px">
					<div class="file-link"
						v-for="pdf in pdfs"
						:key="pdf.url"
						@click="changeFile(pdf.url)">
						{{pdf.name}}
					</div>
				</div>
			</template>
		</AppNavigation>
		<AppContent>
			<div v-if="selectedPdf" :key="selectedPdf">
				<WebViewer :path="`${publicPath}lib`" :url="selectedPdf" />
			</div>
			<Modal v-if="modal" @close="closeModal">
				<div class="modal__content" style="width: 50vw; text-align: center; margin: 10vw 0;">{{modalMessage}}</div>
			</Modal>
		</AppContent>
	</Content>
</template>

<script>
/* eslint-disable */
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
			pdfs: [],
			selectedPdf: '',
		}
	},
	mounted() {
	console.log('Hello inside mounted!!')
		this.fetchFiles()	
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
					this.fetchFiles()
					this.file = null
				} else {
					this.showModal()
					this.modalMessage = 'Something went wrong!'
					this.file = null
				}
			}).catch(err => {
				console.info('Something went wrong', err)
				this.file = null
			})
		},
		fetchFiles() {
		axios.get('http://66.175.217.67/nextclou/index.php/apps/vueexample/getfile').then(res => {
			console.log('response', JSON.parse(res.data))
			const parsedObj = JSON.parse(res.data);
			const newArray = [];
			for (const property in parsedObj) {
					const newObj = {
						url: `http://66.175.217.67/${parsedObj[property]}`,
						name: `http://66.175.217.67/${parsedObj[property]}`.slice(64, `http://66.175.217.67/${parsedObj[property]}`.length)
					}
					// newArray.push(parsedObj[property])
					newArray.push(newObj)
			}
			console.log('asdadasdad', newArray)	
			this.pdfs = [...newArray]
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
		changeFile(url) {
			console.info('url', url)
			this.selectedPdf = url
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

	.file-link {
		line-height: 50px;
		cursor: pointer;
	}

	input[type='file']  {
		display: none;
	}

	.upload {
		width: 100px;
		height: 30px;
		background: #2d85cd;
		padding: 5px 15px;
		border-radius: 15px;
		color: #fff;
		margin-right: 10px;
	}
</style>
