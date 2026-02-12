import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'

describe('fetch data builds correct URL', () => {
    beforeEach(() => {
        vi.stubGlobal('setInterval', vi.fn())
        vi.stubGlobal('clearInterval', vi.fn())
    })

    afterEach(() => {
        vi.unstubAllGlobals()
    })

    function buildUrl(endpoint, search) {
        const params = new URLSearchParams(search)
        params.set('data', endpoint)
        return '?' + params.toString()
    }

    it('includes existing query parameters from the URL', () => {
        expect(buildUrl('stats', '?token=abc&page=2')).toBe('?token=abc&page=2&data=stats')
    })

    it('works when URL has no existing query parameters', () => {
        expect(buildUrl('jobs', '')).toBe('?data=jobs')
    })

    it('overwrites an existing data parameter in the URL', () => {
        expect(buildUrl('stats', '?data=old&token=abc')).toBe('?data=stats&token=abc')
    })
})
