<?php
use Abs\EntityPkg\Database\Seeds\EntityPkgSeeder;
use Abs\EntityPkg\Entity;

class EntitySeeder extends EntityPkgSeeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {

		$entity_types = [
			6 => [
				'Asthma',
				'Bladder Cancer',
				'Mumps',
				'Chronic Bronchitis',
				'Liver Disease',
				'Chicken Pox',
				'Chronic Cough',
				'Kidney Disease',
				'Whooping Cough',
				'Emphysema',
				'Hyperthyroidism',
				'Scarlet Fever',
				'Chronic Sinusitis',
				'Thyroid Disease',
				'Pnemonia',
				'Allergic Sinus problem',
				'Hypothyroidism',
				'Bursitis',
				'Chronic Allergic Rhinitis',
				'Lupus Erythematosus',
				'Polio',
				'Sinus Headaches',
				'Scleroderma',
				'Reduced Vitality',
				'Chronic Colds',
				'Epistaxis (Nosebleed)',
				'Arteriosclerosis',
				'Female Menopause',
				'Chicken Pox',
				'Stroke',
				'Andropause - decreased potency',
				'Bacterial/Fungal Infection',
				'Heart Problems',
				'Nervous Disturbances',
				'Hepatitis',
				'Seizure Disorders',
				'Loss of Memory',
				'Glaucoma',
				'Anxiety Disorder',
				'Psychiatric Disturbances',
				'Loss of Appetite',
				'Elevated PSA Level',
				'Decreased Sexual Potency',
				'Rapid Weight Gain',
				'Anemia',
				'Sleep Disturbances',
				'Rapid Weight Loss',
				'Bulimia',
				'Dizziness',
				'Digestive problem',
				'Anorexia',
				'Chronic Migraine',
				'Acid Indigestion',
				'Cirrhosis of the Liver',
				'Meningitis',
				'Stomach Ulcers',
				'Renal Failure',
				'Jaundice',
				'Overweight problem',
				'Colitis',
				'Epilepsy',
				'Pancreatitis',
				'Herpes',
				'Ear Infection',
				'Pancreatic Insufficiency',
				'Syphilis',
				'Hearing Loss',
				'Leg Cramps',
				'HIV Disease',
				'Nausea',
				'Swollen Ankles',
				'Chlamydia',
				'Rectal Bleeding',
				'Varicose Veins',
				'Angina Pectoris',
				'Burning of Urination',
				'Joint Pain',
				'Tachycardia',
				'Breast Cancer',
				'Back Pain',
				'Hypertension(high blood pressure)',
				'Cervical Cancer',
				'Arthritis',
				'Hypotension(low blood pressure)',
				'Ovarian Cancer',
				'Leg Ulcers',
				'Tuberculosis',
				'Prostate Cancer',
				'Arms/Legs tingling sensation',
				'Breathing Problems',
				'Enlarged Prostate',
				'Hands/Legs falling asleep',
			],
		];

		foreach ($entity_types as $entity_type_id => $entities) {
			// $entity_type = EntityType::firstOrNew([
			// 	'id' => $entity_type_id,
			// ]);
			foreach ($entities as $entity_name) {

				$entity = Entity::firstOrNew([
					'entity_type_id' => $entity_type_id,
					'name' => $entity_name,
				]);
				$entity->save();
			}
		}

	}
}
