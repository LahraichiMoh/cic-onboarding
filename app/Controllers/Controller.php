<?php

namespace App\Controllers;

use App\Exceptions\ValidationException;
use Slim\Http\Request;
use Valitron\Validator;

abstract class Controller
{
    public function validate(Request $request, array $rules)
    {
        $validator = new Validator($request->getParsedBody());

        $validator->mapFieldsRules($rules);

        if (!$validator->validate()) {
            throw new ValidationException(
                $request,
                $validator->errors()
            );
        }

        return $request->getParsedBody();
    }

    // public function moveUploadedFile($directory, UploadedFile $uploadedFile)
    public function moveUploadedFile($directory, $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $uploadPath = 'uploads' . DS . $directory;
        $fullPath = base_path('public' . DS . $uploadPath);

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $uploadedFile->moveTo($fullPath . DS . $filename);

        return [
            'name' => $filename,
            'file_path' => $fullPath . DS . $filename,
            'upload_path' => $uploadPath . DS . $filename,
        ];
    }
}
