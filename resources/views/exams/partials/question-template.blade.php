<script type="text/template" id="questionTemplate">
    <div class="question-item mb-4 p-3 border rounded" data-question-index="<%= index %>">
        <input type="hidden" name="questions[<%= index %>][id]" value="<%= id || '' %>">
        <div class="d-flex justify-content-between mb-2">
            <h5 class="mb-0">Câu hỏi <span class="question-number"><%= index + 1 %></span></h5>
            <button type="button" class="btn btn-sm btn-danger remove-question">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="form-group">
            <label>Nội dung câu hỏi *</label>
            <textarea class="form-control question-content" name="questions[<%= index %>][content]" required><%= content || '' %></textarea>
        </div>
        <div class="form-group">
            <label>Điểm *</label>
            <input type="number" step="0.1" min="0.1" class="form-control" name="questions[<%= index %>][marks]" value="<%= marks || 1 %>" required>
        </div>

        <div class="answers-container">
            <% for(let i = 0; i < 4; i++) { %>
            <%
            var option = options && options[i] ? options[i] : { content: '', is_correct: false, id: '' };
            var isCorrect = correctOption !== undefined ? (correctOption == i) : (option.is_correct || i === 0);
            %>
            <div class="form-group">
                <input type="hidden" name="questions[<%= index %>][options][<%= i %>][id]" value="<%= option.id || '' %>">
                <label>Đáp án <%= String.fromCharCode(65 + i) %> *</label>
                <div class="input-group">
                    <input type="text" class="form-control"
                           name="questions[<%= index %>][options][<%= i %>][content]"
                           value="<%= option.content || '' %>" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <input type="radio"
                                   name="questions[<%= index %>][correct_option]"
                                   value="<%= i %>" <% if (isCorrect) { %>checked<% } %> required>
                        </div>
                    </div>
                </div>
            </div>
            <% } %>
        </div>
    </div>
</script>
