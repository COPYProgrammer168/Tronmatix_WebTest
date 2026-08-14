import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\ChatController::message
 * @see app/Http/Controllers/Api/ChatController.php:18
 * @route '/api/chat/message'
 */
export const message = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: message.url(options),
    method: 'post',
})

message.definition = {
    methods: ["post"],
    url: '/api/chat/message',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\ChatController::message
 * @see app/Http/Controllers/Api/ChatController.php:18
 * @route '/api/chat/message'
 */
message.url = (options?: RouteQueryOptions) => {
    return message.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ChatController::message
 * @see app/Http/Controllers/Api/ChatController.php:18
 * @route '/api/chat/message'
 */
message.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: message.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\ChatController::message
 * @see app/Http/Controllers/Api/ChatController.php:18
 * @route '/api/chat/message'
 */
    const messageForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: message.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\ChatController::message
 * @see app/Http/Controllers/Api/ChatController.php:18
 * @route '/api/chat/message'
 */
        messageForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: message.url(options),
            method: 'post',
        })
    
    message.form = messageForm
const ChatController = { message }

export default ChatController