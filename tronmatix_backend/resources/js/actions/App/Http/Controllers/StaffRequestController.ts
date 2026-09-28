import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\StaffRequestController::showForm
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
export const showForm = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showForm.url(options),
    method: 'get',
})

showForm.definition = {
    methods: ["get","head"],
    url: '/dashboard/request-access',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\StaffRequestController::showForm
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
showForm.url = (options?: RouteQueryOptions) => {
    return showForm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaffRequestController::showForm
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
showForm.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showForm.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\StaffRequestController::showForm
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
showForm.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showForm.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\StaffRequestController::showForm
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
    const showFormForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showForm.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\StaffRequestController::showForm
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
        showFormForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showForm.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\StaffRequestController::showForm
 * @see app/Http/Controllers/StaffRequestController.php:20
 * @route '/dashboard/request-access'
 */
        showFormForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showForm.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showForm.form = showFormForm
/**
* @see \App\Http\Controllers\StaffRequestController::submit
 * @see app/Http/Controllers/StaffRequestController.php:33
 * @route '/dashboard/request-access'
 */
export const submit = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
})

submit.definition = {
    methods: ["post"],
    url: '/dashboard/request-access',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\StaffRequestController::submit
 * @see app/Http/Controllers/StaffRequestController.php:33
 * @route '/dashboard/request-access'
 */
submit.url = (options?: RouteQueryOptions) => {
    return submit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaffRequestController::submit
 * @see app/Http/Controllers/StaffRequestController.php:33
 * @route '/dashboard/request-access'
 */
submit.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\StaffRequestController::submit
 * @see app/Http/Controllers/StaffRequestController.php:33
 * @route '/dashboard/request-access'
 */
    const submitForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submit.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\StaffRequestController::submit
 * @see app/Http/Controllers/StaffRequestController.php:33
 * @route '/dashboard/request-access'
 */
        submitForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submit.url(options),
            method: 'post',
        })
    
    submit.form = submitForm
/**
* @see \App\Http\Controllers\StaffRequestController::accept
 * @see app/Http/Controllers/StaffRequestController.php:78
 * @route '/dashboard/staff-requests/{id}/accept'
 */
export const accept = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: accept.url(args, options),
    method: 'post',
})

accept.definition = {
    methods: ["post"],
    url: '/dashboard/staff-requests/{id}/accept',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\StaffRequestController::accept
 * @see app/Http/Controllers/StaffRequestController.php:78
 * @route '/dashboard/staff-requests/{id}/accept'
 */
accept.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return accept.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaffRequestController::accept
 * @see app/Http/Controllers/StaffRequestController.php:78
 * @route '/dashboard/staff-requests/{id}/accept'
 */
accept.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: accept.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\StaffRequestController::accept
 * @see app/Http/Controllers/StaffRequestController.php:78
 * @route '/dashboard/staff-requests/{id}/accept'
 */
    const acceptForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: accept.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\StaffRequestController::accept
 * @see app/Http/Controllers/StaffRequestController.php:78
 * @route '/dashboard/staff-requests/{id}/accept'
 */
        acceptForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: accept.url(args, options),
            method: 'post',
        })
    
    accept.form = acceptForm
/**
* @see \App\Http\Controllers\StaffRequestController::reject
 * @see app/Http/Controllers/StaffRequestController.php:130
 * @route '/dashboard/staff-requests/{id}/reject'
 */
export const reject = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

reject.definition = {
    methods: ["post"],
    url: '/dashboard/staff-requests/{id}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\StaffRequestController::reject
 * @see app/Http/Controllers/StaffRequestController.php:130
 * @route '/dashboard/staff-requests/{id}/reject'
 */
reject.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return reject.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaffRequestController::reject
 * @see app/Http/Controllers/StaffRequestController.php:130
 * @route '/dashboard/staff-requests/{id}/reject'
 */
reject.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\StaffRequestController::reject
 * @see app/Http/Controllers/StaffRequestController.php:130
 * @route '/dashboard/staff-requests/{id}/reject'
 */
    const rejectForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reject.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\StaffRequestController::reject
 * @see app/Http/Controllers/StaffRequestController.php:130
 * @route '/dashboard/staff-requests/{id}/reject'
 */
        rejectForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reject.url(args, options),
            method: 'post',
        })
    
    reject.form = rejectForm
const StaffRequestController = { showForm, submit, accept, reject }

export default StaffRequestController