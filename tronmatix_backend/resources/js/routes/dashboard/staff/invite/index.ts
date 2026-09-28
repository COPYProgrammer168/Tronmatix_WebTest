import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\StaffController::resend
 * @see app/Http/Controllers/Dashboard/StaffController.php:135
 * @route '/dashboard/staff/invites/{id}/resend'
 */
export const resend = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resend.url(args, options),
    method: 'post',
})

resend.definition = {
    methods: ["post"],
    url: '/dashboard/staff/invites/{id}/resend',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\StaffController::resend
 * @see app/Http/Controllers/Dashboard/StaffController.php:135
 * @route '/dashboard/staff/invites/{id}/resend'
 */
resend.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return resend.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\StaffController::resend
 * @see app/Http/Controllers/Dashboard/StaffController.php:135
 * @route '/dashboard/staff/invites/{id}/resend'
 */
resend.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resend.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\StaffController::resend
 * @see app/Http/Controllers/Dashboard/StaffController.php:135
 * @route '/dashboard/staff/invites/{id}/resend'
 */
    const resendForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resend.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\StaffController::resend
 * @see app/Http/Controllers/Dashboard/StaffController.php:135
 * @route '/dashboard/staff/invites/{id}/resend'
 */
        resendForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resend.url(args, options),
            method: 'post',
        })
    
    resend.form = resendForm
const invite = {
    resend: Object.assign(resend, resend),
}

export default invite