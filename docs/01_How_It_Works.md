# How it works

This PHP project uses a unique approach to render HTML and manage output through encapsulated methods that populate a global object, specifically `$this->g->out['main']`. Here's an explanation of how this system works:

1. Encapsulation:
   - The project uses object-oriented programming principles to encapsulate functionality within classes.
   - Each plugin or theme class contains methods that generate specific parts of the HTML output.

2. Global Object:
   - The project uses a global object `$this->g` to store configuration, input, and output data.
   - The `$this->g->out` array holds various components of the final output, with `$this->g->out['main']` typically containing the main content.

3. Content Generation:
   - As different methods are called within the application, they generate HTML content.
   - Instead of directly outputting this content, they assign it to `$this->g->out['main']` or other relevant keys in the `$this->g->out` array.

4. Accumulation:
   - Throughout the execution of the application, content is accumulated in the `$this->g->out` array.
   - This allows different parts of the application to contribute to the final output without immediately sending it to the browser.

5. Final Rendering:
   - The `Init` class has a `__toString()` method that is called when the object is treated as a string (e.g., when echoed).
   - This method assembles all the accumulated components from `$this->g->out` into the final output.

6. Flexible Output Formats:
   - The `__toString()` method checks the requested output format (HTML, plain text, or JSON) based on the `$this->g->in['x']` value.
   - It then formats the accumulated data accordingly:
     - For HTML, it uses a template to structure the full page.
     - For plain text, it strips tags and formats the main content.
     - For JSON, it encodes the relevant data.

7. Single Output:
   - By accumulating all content and then rendering it at once, the system ensures that headers can be properly set and that the entire page is sent to the browser in one go.

8. API Support:
   - This approach also easily supports API calls by allowing the same underlying methods to generate content, which can then be formatted as JSON when requested.

This architecture provides several benefits:
- Separation of concerns: Content generation is separated from output formatting.
- Flexibility: The same core logic can output different formats (HTML, text, JSON) without major changes.
- Modularity: Different parts of the application can contribute to the output independently.
- Performance: By accumulating content before sending, it potentially reduces the number of writes to the output buffer.

Overall, this approach allows for a clean, modular structure that can easily adapt to different output needs while maintaining a consistent internal logic for content generation.