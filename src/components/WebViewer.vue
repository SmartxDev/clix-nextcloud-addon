<template>
	<div ref='viewer' class="hello-viewer"></div>
</template>

<script>
/* eslint-disable */
import WebViewer from '@pdftron/webviewer'

export default {
  name: 'WebViewer',
  props: {
    path: String,
    url: String
  },
  mounted: function () {
  console.log('webviewer mounted')
        WebViewer({
            path: this.path,
            initialDoc: this.url, // replace with your own PDF file
          }, this.$refs.viewer).then((instance) => {
            // call apis here
          });
    },
      updated: function () {
  console.log('webviewer updated')
        WebViewer({
            path: this.path,
            initialDoc: this.url, // replace with your own PDF file
          }, this.$refs.viewer).then((instance) => {
            // call apis here
          });
    }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
	div { 
		width: 100%;
		height: 100vh;
	}
</style>