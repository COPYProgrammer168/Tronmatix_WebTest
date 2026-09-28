import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\SettingsController::clear
 * @see app/Http/Controllers/Dashboard/SettingsController.php:104
 * @route '/dashboard/notifications/clear'
 */
export const clear = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

clear.definition = {
    methods: ["post"],
    url: '/dashboard/notifications/clear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::clear
 * @see app/Http/Controllers/Dashboard/SettingsController.php:104
 * @route '/dashboard/notifications/clear'
 */
clear.url = (options?: RouteQueryOptions) => {
    return clear.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\SettingsController::clear
 * @see app/Http/Controllers/Dashboard/SettingsController.php:104
 * @route '/dashboard/notifications/clear'
 */
clear.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\SettingsController::clear
 * @see app/Http/Controllers/Dashboard/SettingsController.php:104
 * @route '/dashboard/notifications/clear'
 */
    const clearForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: clear.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\SettingsController::clear
 * @see app/Http/Controllers/Dashboard/SettingsController.php:104
 * @route '/dashboard/notifications/clear'
 */
        clearForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: clear.url(options),
            method: 'post',
        })
    
    clear.form = clearForm
const notifications = {
    clear: Object.assign(clear, clear),
}

export default notifications