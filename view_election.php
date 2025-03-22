<?php

// Replace the existing candidates query with this one
$stmt = $conn->prepare("
    SELECT 
        n.id as nomination_id,
        n.first_name,
        n.surname,
        n.photo_path,
        n.manifesto,
        n.role as position,
        n.branch,
        n.status,
        (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = n.id AND v.election_id = :election_id) as vote_count
    FROM nominations n
    WHERE n.election_id = :election_id 
    AND n.status = 'approved'
    ORDER BY n.role, n.first_name
    LIMIT :limit OFFSET :offset
");

// Update the total candidates count query
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM nominations 
    WHERE election_id = ? AND status = 'approved'
");

// Update the card display section
?>
<!-- Replace the existing candidates display section with this -->
<?php if (!empty($candidates)): ?>
    <h3 class="mb-4">Candidates</h3>
    <div class="row">
        <?php 
        $current_position = '';
        foreach ($candidates as $candidate): 
            if ($current_position != $candidate['position']):
                $current_position = $candidate['position'];
        ?>
            <div class="col-12">
                <h4 class="mt-4 mb-3 text-primary"><?php echo htmlspecialchars($candidate['position']); ?></h4>
            </div>
        <?php endif; ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['surname']); ?>
                        </h5>
                    </div>
                    <?php if ($candidate['photo_path']): ?>
                        <img src="<?php echo htmlspecialchars($candidate['photo_path']); ?>" 
                             class="card-img-top candidate-photo" 
                             alt="<?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['surname']); ?>">
                    <?php else: ?>
                        <div class="card-img-top candidate-photo-placeholder">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($candidate['branch']); ?>
                            </small>
                        </div>
                        
                        <button class="btn btn-outline-primary btn-sm mb-3" 
                                data-bs-toggle="modal" 
                                data-bs-target="#manifestoModal<?php echo $candidate['nomination_id']; ?>">
                            <i class="fas fa-file-alt me-2"></i>View Manifesto
                        </button>
                        
                        <?php if ($election['status'] === 'active' && !$election['has_voted']): ?>
                            <form method="post" class="mt-3">
                                <input type="hidden" name="vote" value="1">
                                <input type="hidden" name="candidate_id" value="<?php echo $candidate['nomination_id']; ?>">
                                <button type="submit" class="btn btn-primary w-100" 
                                        onclick="return confirm('Are you sure you want to vote for <?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['surname']); ?>?')">
                                    <i class="fas fa-vote-yea me-2"></i>Vote
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Manifesto Modal -->
            <div class="modal fade" id="manifestoModal<?php echo $candidate['nomination_id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Candidate Manifesto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <h6 class="mb-3">
                                <?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['surname']); ?>
                                <small class="text-muted">- <?php echo htmlspecialchars($candidate['position']); ?></small>
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <?php echo nl2br(htmlspecialchars($candidate['manifesto'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>No approved candidates have been registered for this election yet.
    </div>
<?php endif; ?>

<!-- Add these styles -->
<style>
.candidate-photo {
    height: 200px;
    object-fit: cover;
}

.candidate-photo-placeholder {
    height: 200px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.candidate-photo-placeholder i {
    font-size: 5rem;
    color: #dee2e6;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.text-primary {
    color: #2c3e50 !important;
}

.btn-primary {
    background-color: #2c3e50;
    border-color: #2c3e50;
}

.btn-primary:hover {
    background-color: #3498db;
    border-color: #3498db;
}

.btn-outline-primary {
    color: #2c3e50;
    border-color: #2c3e50;
}

.btn-outline-primary:hover {
    background-color: #2c3e50;
    border-color: #2c3e50;
}
</style> 