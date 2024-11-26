import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'timer',
        'lives',
        'score',
        'answerInput',
        'answerForm',
        'questionImage',
        'solutionInput' // Add a solution input target for better handling
    ];

    connect() {
        this.timeRemaining = parseInt(this.timerTarget.textContent, 10);
        this.currentSolution = JSON.parse(this.data.get('solution')); // Initialize solution from data attribute
        this.countdownInterval = null;

        this.startTimer();
    }

    startTimer() {
        console.log(this.currentSolution);
        this.countdownInterval = setInterval(() => {
            if (this.timeRemaining <= 0) {
                clearInterval(this.countdownInterval);
                this.handleTimeout();
            } else {
                this.timerTarget.textContent = this.timeRemaining;
                this.timeRemaining--;
            }
        }, 1000);
    }

    handleTimeout() {
        this.sendAnswer(null); // No answer submitted on timeout
    }

    submitForm(event) {
        event.preventDefault(); // Prevent form submission
        clearInterval(this.countdownInterval); // Stop the timer

        const answer = this.answerInputTarget.value;
        this.sendAnswer(answer);
    }

    sendAnswer(answer) {
        fetch(this.data.get('submitUrl'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                answer: answer,
                solution: this.currentSolution // Use the current solution
            })
        })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => this.updateGameState(data))
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
    }

    updateGameState(data) {
        if (data.gameOver) {
            alert(`Game Over! Your Score is: ${data.score}`);
            window.location.href = this.data.get('dashboardUrl');
        } else {
            this.updateLives(data.lives)
            // Update lives, score, and question image
            this.scoreTarget.textContent = data.score;
            if (this.questionImageTarget) {
                this.questionImageTarget.src = data.newQuestionImage; // Update image
            }

            // Update the solution
            this.currentSolution = data.solution; // Update the solution dynamically
            if (this.solutionInputTarget) {
                this.solutionInputTarget.value = data.solution; // Optional: Update a hidden input for debugging or inspection
            }

            // Reset input field
            if (this.answerInputTarget) {
                this.answerInputTarget.value = '';
            }

            // Reset the timer
            this.timeRemaining = parseInt(this.timerTarget.dataset.timeLimit, 10);
            this.startTimer();
        }
    }

    updateLives(lives) {
        // Update the lives displayed as hearts
        const hearts = this.livesTarget.querySelectorAll('.heart');
        hearts.forEach((heart, index) => {
            if (index < lives) {
                heart.classList.remove('disabled'); // Make the heart red (active)
            } else {
                heart.classList.add('disabled'); // Make the heart gray (used)
            }
        });
    }
}
