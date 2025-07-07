<?php
include_once('includes/dbh.php');

$sql = "SELECT q.id AS qid, q.question, a.answer 
        FROM tblquestions q
        LEFT JOIN tblanswers a ON q.id = a.faq_id
        ORDER BY q.id, a.id";
$result = $conn->query($sql);

$faqs = [];
while ($row = $result->fetch_assoc()) {
    $faqs[$row['qid']]['question'] = $row['question'];
    $faqs[$row['qid']]['answers'][] = $row['answer'];
}
?>

<!-- Floating FAQ Button -->
<div id="faq-button" title="Technical Guidance">
    <i class='bx bx-question-mark'></i>
</div>

<!-- FAQ Modal Popup -->
<div id="faq-modal" class="faq-modal hidden">
    <div class="faq-header">
        <span>Frequently Asked Questions (FAQ)</span>
        <button id="faq-close">✖</button>
    </div>
    <div class="faq-body">
        <?php foreach ($faqs as $faq): ?>
            <div class="faq">
                <div class="faq-question"><?= htmlspecialchars($faq['question']) ?></div>
                <div class="faq-answer">
                    <?php foreach ($faq['answers'] as $ans): ?>
                        <div><?= htmlspecialchars($ans) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Include CSS and JS -->
<link rel="stylesheet" href="css/faq.css">
<script src="javascript/faq.js" defer></script>
