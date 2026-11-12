import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardChartController::show
 * @see app/Http/Controllers/DashboardChartController.php:38
 * @route '/dashboard/charts/{chart}'
 */
export const show = (args: { chart: string | number } | [chart: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/dashboard/charts/{chart}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardChartController::show
 * @see app/Http/Controllers/DashboardChartController.php:38
 * @route '/dashboard/charts/{chart}'
 */
show.url = (args: { chart: string | number } | [chart: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { chart: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    chart: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        chart: args.chart,
                }

    return show.definition.url
            .replace('{chart}', parsedArgs.chart.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardChartController::show
 * @see app/Http/Controllers/DashboardChartController.php:38
 * @route '/dashboard/charts/{chart}'
 */
show.get = (args: { chart: string | number } | [chart: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardChartController::show
 * @see app/Http/Controllers/DashboardChartController.php:38
 * @route '/dashboard/charts/{chart}'
 */
show.head = (args: { chart: string | number } | [chart: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardChartController::show
 * @see app/Http/Controllers/DashboardChartController.php:38
 * @route '/dashboard/charts/{chart}'
 */
    const showForm = (args: { chart: string | number } | [chart: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardChartController::show
 * @see app/Http/Controllers/DashboardChartController.php:38
 * @route '/dashboard/charts/{chart}'
 */
        showForm.get = (args: { chart: string | number } | [chart: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardChartController::show
 * @see app/Http/Controllers/DashboardChartController.php:38
 * @route '/dashboard/charts/{chart}'
 */
        showForm.head = (args: { chart: string | number } | [chart: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const charts = {
    show: Object.assign(show, show),
}

export default charts