import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
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
const requestAccess = {
    submit: Object.assign(submit, submit),
}

export default requestAccess