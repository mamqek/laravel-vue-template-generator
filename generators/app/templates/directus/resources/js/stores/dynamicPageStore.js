import { defineStore } from 'pinia'
import { $axios } from '@plugins/axios.js'
import { useLanguageStore  } from '@/stores/languageStore.js'


function normalizePath(path) {
    if (!path) return '/'

    // Only keep the path+query+hash, always leading slash
    try {
        if (path.startsWith('http')) {
            const u = new URL(path)
            return (u.pathname || '/') + (u.search || '') + (u.hash || '')
        }
    } catch {}

    const p = '/' + path.replace(/^\/+/, '')
    return p || '/'
}

/* === ADD: fallback helpers === */
const DEFAULT_LANG = 'en-US'
const isObj = (v) => v && typeof v === 'object' && !Array.isArray(v)

/** Deep merge where values from `primary` win; fall back to `fallback` when null/undefined. */
function mergeWithFallback(primary, fallback) {
  if (primary == null) return fallback ?? null
  if (fallback == null) return primary

  if (Array.isArray(primary) && Array.isArray(fallback)) {
    const len = Math.max(primary.length, fallback.length)
    const out = new Array(len)
    for (let i = 0; i < len; i++) {
      const pv = primary[i]
      const fv = fallback[i]
      if (pv === undefined) out[i] = fv
      else if (isObj(pv) && isObj(fv)) out[i] = mergeWithFallback(pv, fv)
      else out[i] = (pv ?? fv)
    }
    return out
  }

  if (isObj(primary) && isObj(fallback)) {
    const out = {}
    const keys = new Set([...Object.keys(fallback), ...Object.keys(primary)])
    for (const k of keys) {
      out[k] = mergeWithFallback(primary[k], fallback[k])
    }
    return out
  }

  return (primary ?? fallback)
}

export const useDynamicPageStore = defineStore('dynamicPage', {
    state: () => ({
        pagesByTable: {}, // table -> data
        tableByPath: {},  // normalized path -> table
        loadingPaths: new Set(),
        errorsByPath: {},
    }),

    persist: {
        paths: ['pagesByTable', 'tableByPath'],
    },

    getters: {
        getByTable: (s) => (table) => {
            const lang = useLanguageStore().language           // <- live, reactive
            console.log('Getting dynamic page for table', table, 'lang', lang)
            const page = s.pagesByTable[table] || null
            const chosenTranslation = page?.translations?.find(t => t.languages_code?.code === lang)
            const fallbackTranslation = page?.translations?.find(t => t.languages_code?.code === DEFAULT_LANG)
            return mergeWithFallback(chosenTranslation, fallbackTranslation);
        },
        getByPath: (s) => (rawPath) => {
            const path  = normalizePath(rawPath)
            const table = s.tableByPath[path]
            return table ? s.getByTable(table) : null
        },
        isLoading:  (s) => (path) => s.loadingPaths.has(normalizePath(path)),
        errorFor:   (s) => (path) => s.errorsByPath[normalizePath(path)] || null,
        isBlocked:  (s) => (path) => {
            const blocked = [
                '/shop/product/'
            ];

            return blocked.some(b => path.startsWith(b));
        }
    },

    actions: {
        /**
         * Hydrate the store from the server-rendered payload embedded in the HTML
         * so the initial render has synchronous access to page content.
         */
        hydrateFromWindow() {
            // Preferred: read from <script type="application/json" id="initial-page">
            const el = document.getElementById('initial-page')
            if (el && el.textContent) {
                try {
                    const initial = JSON.parse(el.textContent)
                    const table = initial?.table
                    const page  = initial?.page
                    if (table && page) {
                        const path = normalizePath(
                            window.location.pathname + window.location.search + window.location.hash
                        )
                        this.storePagePayload(path, { table, data: page })
                        console.log('Hydrated dynamic page store from #initial-page for path', path, 'table', table, 'data:', page)
                        return
                    }
                } catch (e) {
                    console.warn('Failed to parse #initial-page JSON:', e)
                }
            }
        },

        /**
         * Fetch page data for a given path, updating loading/error state while
         * caching the result for subsequent lookups.
         */
        async fetchByPath(path) {
            const normalizedPath = normalizePath(path)

            if (this.isBlocked(normalizedPath)) {
                console.log('Dynamic page fetch blocked for path', normalizedPath)
                return null
            }

            const existing = this.getByPath(normalizedPath)
            console.log('Fetching dynamic page for path', normalizedPath, 'existing:', existing)

            this.beginLoading(normalizedPath, !!existing)

            try {
                const response = await $axios.get('page', { params: { path: normalizedPath } })
                const payload = this.storePagePayload(normalizedPath, response?.data)
                console.log('Fetched dynamic page for path', normalizedPath, 'table', this.tableByPath[normalizedPath], 'data:', payload)
                return payload
            } catch (error) {
                this.errorsByPath[normalizedPath] = error
                throw error
            } finally {
                this.endLoading(normalizedPath, !!existing)
            }
        },

        /**
         * Record the loading state prior to issuing a network request.
         */
        beginLoading(path, hasExisting) {
            if (!hasExisting) this.loadingPaths.add(path)
            this.errorsByPath[path] = null
        },

        /**
         * Finalize the loading state after a request completes.
         */
        endLoading(path, hasExisting) {
            if (!hasExisting) this.loadingPaths.delete(path)
        },

        /**
         * Persist the fetched page payload into the store.
         */
        storePagePayload(path, payload) {
            const { table, data } = payload || {}
            if (!table || !data) throw new Error('Invalid response')
            this.pagesByTable[table] = data
            this.tableByPath[path] = table
            return data
        },


    },
})
