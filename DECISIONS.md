# Technical Decisions

1. **Asynchronous Processing**: Implemented queue-based processing for CSV files to handle large datasets efficiently and provide better user experience.

2. **Cache for Import Summary**: Used Laravel's cache system to store and update import statistics in real-time, allowing users to monitor progress.

3. **Email-based Deduplication**: Chose email as the unique identifier for contacts as it's typically unique in real-world scenarios.

4. **Gravatar Integration**: Used Gravatar for profile pictures to add visual appeal without requiring file uploads, with identicon fallback for missing avatars.

5. **Vue.js Components**: Split the UI into reusable components (CsvUpload, ImportSummary) for better maintainability and code organization.

6. **Validation Strategy**: Implemented both frontend and backend validation to ensure data integrity and provide immediate feedback to users.

7. **Testing Approach**: Focused on feature tests covering the main functionality (file upload, deduplication, validation) for reliability.
