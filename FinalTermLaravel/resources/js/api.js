const apiRoot = import.meta.env.VITE_API_BASE_URL || '/api/process'

export class ApiError extends Error {
  constructor(message, status) {
    super(message)
    this.status = status
  }
}

export async function request(path, options = {}, retryAfterCsrfRefresh = true) {
  const { headers = {}, ...fetchOptions } = options
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
  const response = await fetch(`${apiRoot}/${path}`, {
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
      ...headers,
    },
    ...fetchOptions,
  })

  if (response.status === 419 && retryAfterCsrfRefresh) {
    const tokenResponse = await fetch(`${apiRoot}/csrf-token`, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
    const tokenData = await tokenResponse.json()
    const csrfMeta = document.querySelector('meta[name="csrf-token"]')

    if (tokenResponse.ok && tokenData.csrfToken && csrfMeta) {
      csrfMeta.content = tokenData.csrfToken
      return request(path, options, false)
    }
  }

  const responseBody = await response.text()
  let data
  try {
    data = JSON.parse(responseBody)
  } catch {
    const message = new DOMParser().parseFromString(responseBody, 'text/html').body.textContent || ''
    if (message.includes("doesn't exist in engine")) {
      throw new ApiError('The inventory tables are unavailable. Check the configured Laravel database.', response.status)
    }
    throw new ApiError('The server returned an unexpected response.', response.status)
  }

  if (data.csrfToken) {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]')
    if (csrfMeta) csrfMeta.content = data.csrfToken
  }

  if (!response.ok || data.error) {
    const validationMessage = Object.values(data.errors || {}).flat()[0]
    throw new ApiError(data.error || validationMessage || data.message || 'The request could not be completed.', response.status)
  }

  return data
}

export function getData(params) {
  const query = new URLSearchParams(params)
  return request(`get_data.php?${query}`)
}

export function getAccountData(params) {
  const query = new URLSearchParams(params)
  return request(`get_account_data.php?${query}`)
}

export function postForm(path, values) {
  const body = values instanceof FormData ? values : new FormData()
  if (!(values instanceof FormData)) {
    Object.entries(values).forEach(([key, value]) => body.append(key, value ?? ''))
  }
  return request(path, { method: 'POST', body })
}
