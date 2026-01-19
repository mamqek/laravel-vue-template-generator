export default {
    install(app) {
    // Use a path RELATIVE to THIS file. If this file is in src/plugins,
    // then ../assets points at src/assets.
        app.config.globalProperties.$assetUrl = (fileObj, download = false) => {
            if (!fileObj) return ""

            const id = typeof fileObj === "string" ? fileObj : fileObj.id
            const params = new URLSearchParams({
                v: fileObj?.modified_on || id,
                ...(download ? { download: '1' } : {}),
                // w: 800,  // example: resize
                // h: 600,  // example: resize
                // fit: "cover"
            })

            return new URL(`../assets/${id}?${params.toString()}`, import.meta.env.VITE_DIRECTUS_URL).href;
        };
    }
}